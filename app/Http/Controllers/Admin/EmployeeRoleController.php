<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Unit;
use App\Transformer\EmployeeTransformer;
use App\Transformer\EmployeeRoleTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class EmployeeRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        try{
            return view('admin.employee_role.index');
        }
        catch (\Exception $ex){
            Log::error('Admin/EmployeeController - index error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function getIndex(Request $request){
        $employee_roles = EmployeeRole::where('id', '>', 0)->orderBy('id', 'asc');
        return DataTables::of($employee_roles)
            ->setTransformer(new EmployeeRoleTransformer())
            ->make(true);
    }

    public function show(int $id)
    {
        $employeeRole = EmployeeRole::find($id);

        if(empty($employeeRole)){
            return redirect()->back();
        }

        $data = [
            'employeeRole'          => $employeeRole,
        ];
        return view('admin.employee_role.show')->with($data);
    }

    public function create(){
        return view('admin.employee_role.create');
    }
    

    // public function store(Request $request){
    //     try{
    //         $validator = Validator::make($request->all(), [
    //             'name'    => 'required|max:100',
    //             'description'     => 'max:100',
    //         ]);

    //         if ($validator->fails())
    //             return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

    //         // // Validate employee role
    //         // if($request->input('role') == -1){
    //         //     return back()->withErrors("Mohon pilih role/posisi!")->withInput($request->all());
    //         // }

    //         // // Validate employee photo
    //         // if(!$request->hasFile('photo')){
    //         //     return back()->withErrors("Foto wajib diunggah!")->withInput($request->all());
    //         // }

    //         // $adminUser = Auth::guard('admin')->user();
    //         // $now = Carbon::now('Asia/Jakarta');

    //         // $dob = null;
    //         // if($request->filled('dob')){
    //         //     $dob = Carbon::createFromFormat('d M Y', $request->input('dob'), 'Asia/Jakarta');
    //         // }

    //         $employee = Employee::create([
    //             'name'        => $request->input('name'),
    //             'description'         => $request->input('description'),
    //         ]);

    //         // if($request->hasFile('photo')){
    //         //     $img = Image::make($request->file('photo'));
    //         //     $extStr = $img->mime();
    //         //     $ext = explode('/', $extStr, 2);

    //         //     $filename = $employee->id.'_photo_'. $now->format('Ymdhms'). '.'. $ext[1];

    //         //     $img->save(public_path('storage/employees/'. $filename), 75);
    //         //     $employee->image_path = $filename;
    //         //     $employee->save();
    //         // }

    //         Session::flash('success', 'Sukses membuat jabatan karyawan baru!');
    //         return redirect()->route('admin.employee_role.show',['id' => $employeeRole->id]);
    //     }
    //     catch (\Exception $ex){
    //         Log::error('Admin/EmployeeRoleController - store error EX: '. $ex);
    //         return "Something went wrong! Please contact administrator!";
    //     }
    // }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'              => 'required|max:100',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama jabatan wajib diisi!',
                'name.unique'       => 'Nama jabatan sudah terdaftar!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            EmployeeRole::create([
                'name'          => $request->input('name'),
                'description'   => $request->input('description') ?? null,
            ]);

            Session::flash('success', 'Sukses membuat nama jabatan baru!');
            return redirect()->route('admin.employee_role.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/EmployeeRoleController - store error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function edit(int $id)
    {
        $employeeRole = EmployeeRole::find($id);

        if(empty($employeeRole)){
            return redirect()->back();
        }

        $data = [
            'employeeRole'          => $employeeRole,
        ];
        return view('admin.employee_role.edit')->with($data);
    }

    public function update(Request $request, int $id){
        try{
            $employeeRole = EmployeeRole::find($id);
            
            $validator = Validator::make($request->all(), [
                'name'              => 'required',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama Jabatan wajib diisi!',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = $request->input('name');

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $employeeRole->name = $request->input('name');
            $employeeRole->description = $request->input('description') ?? null;
            $employeeRole->save();

            Session::flash('success', 'Sukses mengubah nama jabatan!');
            return redirect()->route('admin.employee_role.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/Sub1UnitController - update error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $employeeRole = EmployeeRole::find($deletedId);
            $employeeRole->status_id = 2;
            $employeeRole->save();

            Session::flash('success', 'Sukses mengganti status employee!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/EmployeeRoleController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }

    public function getUpperEmployees(Request $request){
        $term = trim($request->q);

        $employees = Employee::whereIn('employee_role_id', [2,3,4]);

        if($request->ids !== null){
            foreach ($request->ids as $id){
                error_log($id);
            }

            $employees = $employees->whereNotIn('id', $request->ids);
        }

        $employees = $employees->where(function ($q) use ($term) {
                $q->where('code', 'LIKE', '%' . $term . '%')
                    ->orWhere('first_name', 'LIKE', '%' . $term . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $term . '%');
            })
            ->get();

        $formatted_tags = [];

        foreach ($employees as $employee) {
            $formatted_tags[] = [
                'id' => $employee->id. '#'. strtoupper($employee->employee_role->name),
                'text' => $employee->code. ' - '. strtoupper($employee->employee_role->name). ' - '. $employee->first_name. ' '. $employee->last_name
            ];
        }

        return \Response::json($formatted_tags);
    }

    public function getCleanerEmployees(Request $request){
        $term = trim($request->q);

        $employees = Employee::where('employee_role_id', 1);

        if($request->ids !== null){
//            foreach ($request->ids as $id){
//                error_log($id);
//            }

            $employees = $employees->whereNotIn('id', $request->ids);
        }

        $employees = $employees->where(function ($q) use ($term) {
            $q->where('code', 'LIKE', '%' . $term . '%')
                ->orWhere('first_name', 'LIKE', '%' . $term . '%')
                ->orWhere('last_name', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($employees as $employee) {
            $formatted_tags[] = [
                'id' => $employee->id,
                'text' => $employee->code. ' - '. $employee->first_name. ' '. $employee->last_name
            ];
        }

        return \Response::json($formatted_tags);
    }

    public function getEmployees(Request $request){
        $term = trim($request->q);

        $employees = Employee::where(function ($q) use ($term) {
            $q->where('code', 'LIKE', '%' . $term . '%')
                ->orWhere('first_name', 'LIKE', '%' . $term . '%')
                ->orWhere('last_name', 'LIKE', '%' . $term . '%');
            })
            ->get();

        $formatted_tags = [];

        foreach ($employees as $employee) {
            $formatted_tags[] = ['id' => $employee->id, 'text' => $employee->code. ' - '. $employee->first_name. ' '. $employee->last_name];
        }

        return \Response::json($formatted_tags);
    }
}
