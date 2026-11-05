<?php
namespace App\Http\Controllers;

use App\Models\{Branch,Employee,InternalAnnouncement,Section};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InternalCommunicationController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('show_internal_comms')||auth()->user()->can('manage_internal_comms'),403);
        $announcements=InternalAnnouncement::when(!auth()->user()->can('manage_internal_comms'),fn($q)=>$q->where('status','published'))->with(['branch','section','creator'])->withCount('acknowledgments')->latest()->paginate(20);
        foreach($announcements as $item){$audience=Employee::where('status','active')->when($item->branch_id,fn($q)=>$q->where('branch_id',$item->branch_id))->when($item->section_id,fn($q)=>$q->where('section_id',$item->section_id));$item->target_count=(clone $audience)->count();$item->audience_ack_count=$item->acknowledgments()->whereIn('employee_id',(clone $audience)->select('id'))->count();}
        $branches=Branch::orderBy('name')->get();$sections=Section::orderBy('name')->get();
        return view('dashboard.internal-comms.index',compact('announcements','branches','sections'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('manage_internal_comms'),403);
        $data=$request->validate(['kind'=>'required|in:announcement,policy,form','title'=>'required|string|max:180','body'=>'required|string|max:15000','branch_id'=>'nullable|exists:branches,id','section_id'=>'nullable|exists:sections,id','requires_acknowledgment'=>'required|boolean','expires_at'=>'nullable|date|after:today','file'=>'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:10240']);
        if($request->hasFile('file')){$data['file_path']=$request->file('file')->store('internal-comms');$data['original_filename']=$request->file('file')->getClientOriginalName();}unset($data['file']);
        InternalAnnouncement::create($data+['status'=>'draft','created_by'=>auth()->id()]);return back()->with('success','تم حفظ المحتوى كمسودة.');
    }

    public function publish(InternalAnnouncement $announcement)
    {
        abort_unless(auth()->user()->can('manage_internal_comms'),403);abort_unless($announcement->status==='draft',409);
        abort_if($announcement->expires_at&&$announcement->expires_at->lte(now()),422,'تاريخ انتهاء المحتوى مضى؛ عدّل تاريخ الانتهاء قبل النشر.');
        $announcement->update(['status'=>'published','published_at'=>now()]);return back()->with('success','تم نشر المحتوى للموظفين المعنيين.');
    }

    public function archive(InternalAnnouncement $announcement)
    {
        abort_unless(auth()->user()->can('manage_internal_comms'),403);$announcement->update(['status'=>'archived']);return back()->with('success','تم أرشفة المحتوى.');
    }

    public function download(InternalAnnouncement $announcement)
    {
        abort_unless(auth()->user()->can('show_internal_comms')||auth()->user()->can('manage_internal_comms'),403);
        abort_unless($announcement->status==='published'&&$announcement->file_path&&Storage::exists($announcement->file_path),404);
        if(!auth()->user()->can('manage_internal_comms')){$employee=auth()->user()->employee;abort_unless(auth()->user()->can('employee_portal')&&$employee&&(!$announcement->branch_id||$announcement->branch_id===$employee->branch_id)&&(!$announcement->section_id||$announcement->section_id===$employee->section_id),403);}
        return Storage::download($announcement->file_path,$announcement->original_filename??$announcement->title);
    }
}
