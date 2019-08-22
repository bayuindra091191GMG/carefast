<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeeRole;
use App\Models\Unit;
use App\Transformer\CustomerTransformer;
use App\Transformer\EmployeeTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{
    public function getCustomers(Request $request){
        $term = trim($request->q);
        $customers = Customer::where(function ($q) use ($term) {
            $q->where('name', 'LIKE', '%' . $term . '%')
                ->where('email', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($customers as $customer) {
            $formatted_tags[] = ['id' => $customer->id, 'text' => $customer->name . ' - ' . $customer->email];
        }

        return \Response::json($formatted_tags);
    }

    public function index(){
        try{
            return view('admin.customer.index');
        }
        catch (\Exception $ex){
            Log::error('Admin/CustomerController - index error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function getIndex(Request $request){
        $customers = Customer::all();
        return DataTables::of($customers)
            ->setTransformer(new CustomerTransformer())
            ->make(true);
    }

    public function show(int $id)
    {
        $customer = Customer::find($id);

        if(empty($customer)){
            return redirect()->back();
        }

        $data = [
            'customer'          => $customer,
        ];
        return view('admin.customer.show')->with($data);
    }

    public function create(){
        try{
            $customerRoles = CustomerType::all();

            return view('admin.customer.create', compact('customerRoles'));
        }
        catch (\Exception $ex){
            Log::error('Admin/CustomerController - create error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'    => 'required|max:100',
                'phone'     => 'required',
                'password'          => 'required'
            ]);

            if ($validator->fails())
                return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $validator->sometimes('password', 'min:6|confirmed', function ($input) {
                return $input->password;
            });

            // Validate customer role
            if($request->input('role') == -1){
                return back()->withErrors("Mohon pilih role/posisi!")->withInput($request->all());
            }

            // Validate customer photo
            if(!$request->hasFile('photo')){
                return back()->withErrors("Foto wajib diunggah!")->withInput($request->all());
            }

            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $dob = null;
            if($request->filled('dob')){
                $dob = Carbon::createFromFormat('d M Y', $request->input('dob'), 'Asia/Jakarta');
            }

            $customer = Customer::create([
                'category_id'  => $request->input('role'),
                'email'              => strtoupper($request->input('email')),
                'name'        => strtoupper($request->input('name')),
                'phone'             => $request->input('phone') ?? '',
                'status_id'         => $request->input('status'),
                'password'         => Hash::make($request->input('password')),
                'created_by'        => $adminUser->id,
                'created_at'        => $now->toDateTimeString(),
                'updated_by'        => $adminUser->id,
                'updated_at'        => $now->toDateTimeString(),
            ]);

            if($request->hasFile('photo')){
                $img = Image::make($request->file('photo'));
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);

                $filename = $customer->id.'_photo_'. $now->format('Ymdhms'). '.'. $ext[1];

                $img->save(public_path('storage/customers/'. $filename), 75);
                $customer->image_path = $filename;
                $customer->save();
            }

            Session::flash('success', 'Sukses membuat customer baru!');
            return redirect()->route('admin.customer.show',['id' => $customer->id]);
        }
        catch (\Exception $ex){
            Log::error('Admin/CustomerController - store error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function edit(int $id)
    {
        $customer = Customer::find($id);

        if(empty($customer)){
            return redirect()->back();
        }

        $data = [
            'customer'          => $customer,
        ];
        return view('admin.customer.edit')->with($data);
    }

    public function update(Request $request, int $id){
        try{
            $validator = Validator::make($request->all(), [
                'name'    => 'required|max:100',
                'phone'     => 'required',
            ]);

            if ($validator->fails())
                return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            if(!ctype_space($request->input('password'))){
                $validator->sometimes('password', 'min:6|confirmed', function ($input) {
                    return $input->password;
                });
            }

            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $customer = Customer::find($id);
            if(empty($customer)){
                return redirect()->back();
            }

            if($request->filled('password')){
                $customer->password = Hash::make($request->input('password'));
            }

            $customer->email = $request->input('email');
            $customer->name = strtoupper($request->input('name'));
            $customer->phone = $request->input('phone') ?? '';
            $customer->status_id = $request->input('status');
            $customer->updated_at = $now->toDateTimeString();

            if($request->hasFile('photo')){
                // Delete old image
                if(!empty($customer->image_path)){
                    $deletedPath = public_path('storage/customers/'. $customer->image_path);
                    if(file_exists($deletedPath)) unlink($deletedPath);
                }

                $img = Image::make($request->file('photo'));
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);

                $filename = $customer->id.'_photo_'. $now->format('Ymdhms'). '.'. $ext[1];

                $img->save(public_path('storage/customers/'. $filename), 75);
                $customer->image_path = $filename;
            }

            $customer->save();

            Session::flash('success', 'Sukses mengubah data customer!');
            return redirect()->route('admin.customer.show',['id' => $customer->id]);
        }
        catch (\Exception $ex){
            Log::error('Admin/CustomerController - update error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $customer = Customer::find($deletedId);
            $customer->status_id = 2;
            $customer->save();

            Session::flash('success', 'Sukses mengganti status customer!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/CustomerController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}
