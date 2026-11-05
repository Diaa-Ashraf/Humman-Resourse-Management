<?php
namespace App\Http\Controllers;

use App\Models\{Employee, EmployeeDocument};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\BranchAccess;

class EmployeeDocumentController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('show_employee_documents'), 403);
        $access=app(BranchAccess::class);$documents = $access->throughEmployee(EmployeeDocument::query(),auth()->user())->with(['employee.branch'])->when($request->filled('employee_id'), fn($q)=>$q->where('employee_id',$request->employee_id))
            ->when($request->filled('expiry'), fn($q)=>$request->expiry==='expired' ? $q->whereDate('expires_at','<',today()) : $q->whereBetween('expires_at',[today(),today()->addDays(60)]))
            ->latest('expires_at')->paginate(20)->withQueryString();
        $employees = $access->employees(Employee::query(),auth()->user())->with('branch')->orderBy('name')->get();
        return view('dashboard.employee-documents.index', compact('documents','employees'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('store_employee_documents'), 403);
        $data = $request->validate([
            'employee_id'=>'required|exists:employees,id','category'=>'required|string|max:80','title'=>'required|string|max:180',
            'reference_number'=>'nullable|string|max:100','issued_at'=>'nullable|date','expires_at'=>'nullable|date|after_or_equal:issued_at','notes'=>'nullable|string|max:2000',
            'file'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
        app(BranchAccess::class)->assertEmployee(Employee::findOrFail($data['employee_id']),auth()->user());
        if($request->hasFile('file')){
            $file=$request->file('file');
            $data['file_path']=$file->store('employee-documents');
            $data['original_filename']=$file->getClientOriginalName();
            $data['mime_type']=$file->getMimeType();
            $data['file_size']=$file->getSize();
        }
        $document=EmployeeDocument::create($data);
        $document->employee->changeLogs()->create(['user_id'=>auth()->id(),'event'=>'document_added','notes'=>'إضافة مستند: '.$document->category.' — '.$document->title]);
        return back()->with('success','تم حفظ مستند الموظف وسجل صلاحيته.');
    }

    public function destroy(EmployeeDocument $document)
    {
        app(BranchAccess::class)->assertEmployee($document->employee,auth()->user());
        abort_unless(auth()->user()->can('delete_employee_documents'), 403);
        $document->employee->changeLogs()->create(['user_id'=>auth()->id(),'event'=>'document_removed','notes'=>'حذف مستند: '.$document->category.' — '.$document->title]);
        if($document->file_path) Storage::delete($document->file_path);
        $document->delete();
        return back()->with('success','تم حذف سجل المستند.');
    }

    public function download(EmployeeDocument $document)
    {
        abort_unless(auth()->user()->can('show_employee_documents'),403);
        app(BranchAccess::class)->assertEmployee($document->employee,auth()->user());
        abort_unless($document->file_path && Storage::exists($document->file_path),404);
        return Storage::download($document->file_path,$document->original_filename ?? $document->title);
    }
}
