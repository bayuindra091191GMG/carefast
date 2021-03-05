<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeSchedule;
use App\Models\ProjectEmployee;
use App\Models\Unit;
use App\Models\User;
use App\Transformer\EmployeeTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

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
        $employees = Employee::with(['employee_role', 'status'])->where('id', ">", 0);
        return DataTables::of($employees)
            ->setTransformer(new EmployeeTransformer())
            ->make(true);
    }

    public function show(int $id)
    {
        $employee = Employee::find($id);

        if(empty($employee)){
            return redirect()->back();
        }

        $user = User::where('employee_id', $id)->first();
        if(empty($user)){
            return redirect()->back();
        }
        $currentProject = ProjectEmployee::with('project')
            ->where('employee_id', $id)
            ->where('status_id', 1)
            ->first();

        $data = [
            'employee'          => $employee,
            'currentProject'    => $currentProject,
            'email'             => $user->email
        ];

        return view('admin.employee.show')->with($data);
    }

    public function detail(int $id)
    {
        $employee = Employee::find($id);

        if(empty($employee)){
            return redirect()->back();
        }

        $user = User::where('employee_id', $id)->first();
        if(empty($user)){
            return redirect()->back();
        }

        $data = [
            'employee'          => $employee,
            'email'             => $user->email
        ];

        return view('admin.employee.detail-attendance')->with($data);
    }

    public function create(){
        try{
            $employeeRoles = EmployeeRole::where('id', '>', 3)->where('id', '!=', 9)->where('id', '!=', 10)->get();

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
                'phone'         => 'required|max:20|unique:employees',
                'code'          => 'required|max:50|unique:employees',
                'first_name'    => 'required|max:100',
//                'last_name'     => 'required|max:100',
                'password'      => 'required_with:password_confirmation|same:password_confirmation',
                'password_confirmation' => 'required'
            ],[
                'phone.unique'                      => 'Nomor Ponsel Login sudah terdaftar!',
                'code.unique'                       => 'ID Karyawan sudah terdaftar!',
                'phone.required'                    => 'Nomor Ponsel Login wajib diisi!',
                'password.required_with'            => 'Kata Sandi wajib diisi!',
                'password.same'                     => 'Konfirmasi Kata Sandi berbeda!',
                'password_confirmation.required'    => 'Konfirmasi Kata Sandi wajib diisi!'
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
//                'last_name'         => strtoupper($request->input('last_name')),
                'address'           => $request->input('address') ?? '',
                'telephone'         => $request->input('telephone') ?? '',
                'phone'             => $request->input('phone') ?? '',
                'dob'               => $dob,
                'nik'               => $request->input('nik') ?? '',
                'notes'             => $request->input('notes') ?? '',
                'status_id'         => $request->input('status'),
                'created_by'        => $adminUser->id,
                'created_at'        => $now->toDateTimeString(),
                'updated_by'        => $adminUser->id,
                'updated_at'        => $now->toDateTimeString(),
            ]);

            $name = strtoupper($request->input('first_name')). ' '. strtoupper($request->input('last_name'));

            User::create([
                'employee_id'       => $employee->id,
                'name'              => $name,
                'email'             => $request->input('email') ?? '',
                'password'          => Hash::make($request->input('password')),
                'phone'             => $request->input('phone'),
                'status_id'         => $request->input('status')
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

        $user = User::where('employee_id', $id)->first();
        if(empty($user)){
            return redirect()->back();
        }
        $currentProject = ProjectEmployee::with('project')
            ->where('employee_id', $id)
            ->where('status_id', 1)
            ->first();

        $data = [
            'employee'          => $employee,
            'currentProject'    => $currentProject,
            'email'             => $user->email
        ];

        return view('admin.employee.edit')->with($data);
    }

    public function update(Request $request, int $id){
        try{
            $validator = Validator::make($request->all(), [
                'phone'         => 'required|max:20|unique:employees,phone,'. $id,
                'code'          => 'required|max:50|unique:employees,code,'. $id,
                'first_name'    => 'required|max:100'
            ],[
                'phone.unique'                      => 'Nomor Ponsel Login sudah terdaftar!',
                'code.unique'                       => 'ID Karyawan sudah terdaftar!',
                'phone.required'                    => 'Nomor Ponsel Login wajib diisi!',
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

            $user = User::where('employee_id', $id)->first();
            if(empty($user)){
                return redirect()->back();
            }

            // Update Employee
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

            // Update User
            $name = strtoupper($request->input('first_name')). ' '. strtoupper($request->input('last_name'));

            $user->name = $name;
            $user->phone = $request->input('phone');
            $user->email = $request->input('email') ?? '';
            $user->status_id = $request->input('status');

            if($request->filled('password') && $request->filled('password_confirmation')){
                $passwordNew = $request->input('password');
                $passwordConfirm = $request->input('password_confirmation');
                if($passwordNew !== $passwordConfirm){
                    return back()->withErrors("Konfirmasi Kata Sandi harus sama dengan Kata Sandi Baru!")->withInput($request->all());
                }

                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

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

    public function getUpperEmployees(Request $request){
        $term = trim($request->q);

        $employees = Employee::where('employee_role_id', '>', 1);

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

    public function getUpperManagementEmployees(Request $request){
        $term = trim($request->q);

        $employees = Employee::whereIn('employee_role_id', [5,6,7]);

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

    public function downloadNucPhone(Request $request){
        $now = Carbon::now('Asia/Jakarta');
        $employees = Employee::where('id', '>', 29)->where('status_id', 1)->get();
        $data = "NUC\tEmployee Name\tEmployee Phone\n";
        foreach($employees as $employee){
            $data .= $employee->code."\t"
                .$employee->first_name." ".$employee->last_name."\t"
                .$employee->phone."\n";
        }
        $file = "Employee Download_".$now->format('Y-m-d')."-".time().'.txt';
        $destinationPath=public_path()."/download_employee/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file, $data);
        return response()->download($destinationPath.$file);
    }


    public function scheduleEdit(int $employee_id){
        try{
            $employee = Employee::find($employee_id);

            if(empty($employee)){
                return redirect()->back();
            }

            $currentProject = ProjectEmployee::with('project')
                ->where('employee_id', $employee_id)
                ->where('status_id', 1)
                ->first();

            $isEmpty = true;
            $scheduleModel = collect();
            $employeeSchedule = EmployeeSchedule::where('employee_id', $employee_id)->first();
            if(!empty($employeeSchedule)){
                if(!empty($employeeSchedule->day_status)){
                    $isEmpty = false;
                    $days = explode(";",$employeeSchedule->day_status);
                    foreach ($days as $day){
                        if(!empty($day)){
                            $dayStatus = explode(":", $day);
                            $schedule = [
                                'day'     => $dayStatus[0],
                                'status'  => $dayStatus[1],
                            ];
                            $scheduleModel->push($schedule);
                        }
                    }
                }
            }
            if($isEmpty){
                for($a=1; $a<=31; $a++){
                    $schedule = [
                        'day'     => $a,
                        'status'    => "M",
                    ];
                    $scheduleModel->push($schedule);
                }
            }
            $data = [
                'currentProject'    => $currentProject,
                'employee'          => $employee,
                'scheduleModel'     => $scheduleModel,
            ];
//        dd($data);
            return view('admin.employee.edit-schedule')->with($data);
        }
        catch(\Exception $ex){
            Log::error('Admin/EmployeeController - scheduleEdit error EX: '. $ex);
            return redirect()->back()->withErrors($ex);
        }
    }

    public function scheduleStore(Request $request, int $employee_id){
        try{
            $employee = Employee::find($employee_id);
//        dd($request, $employee_id);

            if(empty($employee)){
                return redirect()->back();
            }

            $dayStatuses = "";
            $i = 0;
            foreach ($request->days as $day){
                $dayStatuses .= $day.":".$request->statuses[$i].";";
                $i++;
            }

            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $employeeSchedule = EmployeeSchedule::where('employee_id', $employee_id)->first();
            if(!empty($employeeSchedule)){
                $employeeSchedule->day_status = $dayStatuses;
                $employeeSchedule->updated_by = $adminUser->id;
                $employeeSchedule->updated_at = $now->toDateTimeString();
                $employeeSchedule->save();
            }
            else{
                $projectActivityHeader = EmployeeSchedule::create([
                    'employee_id'   => $employee->id,
                    'employee_code' => $employee->code,
                    'day_status'    => $dayStatuses,
                    'created_by'    => $adminUser->id,
                    'created_at'    => $now->toDateTimeString(),
                ]);
            }

            Session::flash('success', 'Sukses mengubah jadwal karyawan!');
            return redirect()->route('admin.employee.show',['id' => $employee->id]);
        }
        catch(\Exception $ex){
            Log::error('Admin/EmployeeController - scheduleStore error EX: '. $ex);
            return redirect()->back()->withErrors($ex)->withInput($request->all());
        }
    }
}
