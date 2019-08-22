<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Unit;
use App\Transformer\EmployeeTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{
    public function index(){
        try{
            return view('admin.employee.index');
        }
        catch (\Exception $ex){
            Log::error('Admin/EmployeeController - index error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function getIndex(Request $request){
        $products = Employee::all();
        return DataTables::of($products)
            ->setTransformer(new EmployeeTransformer())
            ->make(true);
    }

    public function show(int $id)
    {
        $employee = Employee::find($id);

        if(empty($employee)){
            return redirect()->back();
        }

        $data = [
            'employee'          => $employee,
        ];
        return view('admin.employee.show')->with($data);
    }

    public function create(){
        try{
            $employeeRoles = EmployeeRole::all();

            return view('admin.employee.create', compact('employeeRoles'));
        }
        catch (\Exception $ex){
            Log::error('Admin/EmployeeController - create error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'code'          => 'required|max:50|unique:employees',
                'first_name'    => 'required|max:100',
                'last_name'     => 'required|max:100',
            ]);

            if ($validator->fails())
                return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            // Validate employee role
            if($request->input('role') == -1){
                return back()->withErrors("Mohon pilih role/posisi!")->withInput($request->all());
            }

            // Validate employee photo
            if(!$request->hasFile('photo')){
                return back()->withErrors("Foto wajib diunggah!")->withInput($request->all());
            }

            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $dob = null;
            if($request->filled('dob')){
                $dob = Carbon::createFromFormat('d M Y', $request->input('dob'), 'Asia/Jakarta');
            }

            $employee = Employee::create([
                'employee_role_id'  => $request->input('role'),
                'code'              => strtoupper($request->input('code')),
                'first_name'        => strtoupper($request->input('first_name')),
                'last_name'         => strtoupper($request->input('last_name')),
                'address'           => $request->input('address') ?? '',
                'telephone'         => $request->input('telephone') ?? '',
                'phone'             => $request->input('phone') ?? '',
                'dob'               => $dob,
                'nik'               => $request->input('nik') ?? '',
                'notes'               => $request->input('notes') ?? '',
                'status_id'         => $request->input('status'),
                'created_by'        => $adminUser->id,
                'created_at'        => $now->toDateTimeString(),
                'updated_by'        => $adminUser->id,
                'updated_at'        => $now->toDateTimeString(),
            ]);

            if($request->hasFile('photo')){
                $img = Image::make($request->file('photo'));
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);

                $filename = $employee->id.'_photo_'. $now->format('Ymdhms'). '.'. $ext[1];

                $img->save(public_path('storage/employees/'. $filename), 75);
                $employee->image_path = $filename;
                $employee->save();
            }

            Session::flash('success', 'Sukses membuat karyawan baru!');
            return redirect()->route('admin.employee.show',['id' => $employee->id]);
        }
        catch (\Exception $ex){
            Log::error('Admin/EmployeeController - store error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function edit(int $id)
    {
        $employee = Employee::find($id);

        if(empty($employee)){
            return redirect()->back();
        }

        $data = [
            'employee'          => $employee,
        ];
        return view('admin.employee.edit')->with($data);
    }

    public function update(Request $request, int $id){
        try{
            $validator = Validator::make($request->all(), [
                'first_name'    => 'required|max:100',
                'last_name'     => 'required|max:100',
            ]);

            if ($validator->fails())
                return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $dob = null;
            if($request->filled('dob')){
                $dob = Carbon::createFromFormat('d M Y', $request->input('dob'), 'Asia/Jakarta');
            }

            $employee = Employee::find($id);
            if(empty($employee)){
                return redirect()->back();
            }

            $employee->first_name = strtoupper($request->input('first_name'));
            $employee->last_name = strtoupper($request->input('last_name'));
            $employee->address = $request->input('address') ?? '';
            $employee->telephone = $request->input('telephone') ?? '';
            $employee->phone = $request->input('phone') ?? '';
            $employee->dob = $dob;
            $employee->nik = $request->input('nik') ?? '';
            $employee->notes = $request->input('notes') ?? '';
            $employee->status_id = $request->input('status');
            $employee->updated_by = $adminUser->id;
            $employee->updated_at = $now->toDateTimeString();

            if($request->hasFile('photo')){
                // Delete old image
                if(!empty($employee->image_path)){
                    $deletedPath = public_path('storage/employees/'. $employee->image_path);
                    if(file_exists($deletedPath)) unlink($deletedPath);
                }

                $img = Image::make($request->file('photo'));
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);

                $filename = $employee->id.'_photo_'. $now->format('Ymdhms'). '.'. $ext[1];

                $img->save(public_path('storage/employees/'. $filename), 75);
                $employee->image_path = $filename;
            }

            $employee->save();

            Session::flash('success', 'Sukses mengubah data karyawan!');
            return redirect()->route('admin.employee.show',['id' => $employee->id]);
        }
        catch (\Exception $ex){
            Log::error('Admin/EmployeeController - update error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $employee = Employee::find($deletedId);
            $employee->status_id = 2;
            $employee->save();

            Session::flash('success', 'Sukses mengganti status employee!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/EmployeeController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}
