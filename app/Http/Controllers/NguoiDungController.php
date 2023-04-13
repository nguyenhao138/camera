<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CauHinh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Components\Traits\DeleteModelTrait;
use App\Models\DanhMuc;
use App\Models\QuanHuyen;
use App\Models\ThuongHieu;
use App\Models\TinhThanhPho;
use App\Models\XaPhuong;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;

class NguoiDungController extends Controller
{
    use DeleteModelTrait;
    private $user, $cauhinh, $dmuc, $thuonghieu;
    public function __construct(User $user, CauHinh $cauhinh, DanhMuc $dmuc, ThuongHieu $thuonghieu)
    {
        $this->user = $user;
        $this->cauhinh = $cauhinh;
        $this->dmuc = $dmuc;
        $this->thuonghieu = $thuonghieu;
    }

    //Bắt đầu trang admin
    public function index()
    {
        $id_sua = '0';
        $capnhatquyen = '';
        $page = 5;
        $users = $this->user::orderBy('ho_ten')->paginate($page);
        return view('backend.nguoidung.home', compact("users", "capnhatquyen", "id_sua"))->with('i', (request()->input('page', 1) - 1) * $page);
    }

    public function getThem()
    {
        return view('backend.nguoidung.them');
    }

    public function postThem(Request $request)
    {
        $request->validate([
            'ho_ten' => 'required|max:191|unique:users',
            'email' => 'required|max:191|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:users',
            'password' => 'required',
            'opt_quyen' => 'required'
        ], [
            'ho_ten.required' => 'Hãy nhập tên người dùng',
            'ho_ten.max' => 'Tên người dùng quá dài',
            'ho_ten.unique' => 'Tên người dùng đã được sử dụng',
            'email.required' => 'Hãy nhập email',
            'email.regex' => 'Email không đúng dạng. VD: abc@gmail.com',
            'email.max' => 'Email quá dài',
            'email.unique' => 'Email đã tồn tại',
            'password.required' => 'Hãy nhập password',
            'opt_quyen.required' => 'Hãy chọn quyền',
        ]);
        try {
            DB::beginTransaction();
            $this->user->firstOrCreate([
                'ho_ten' => trim($request->ho_ten),
                'email' => trim($request->email),
                'password' => bcrypt(trim($request->password)),
                'quyen' => trim($request->opt_quyen),
            ]);
            DB::commit();
            Alert::success('Thành công', 'Thêm người dùng thành công');
            return redirect()->route('nguoidung.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            Alert::error('Thất bại', 'Thêm người dùng thất bại');
            return redirect()->route('nguoidung.getThem');
        }
    }

    public function gethoso($id)
    {
        $tinh_tp = TinhThanhPho::orderby('ten_tp')->get();
        $huyen = QuanHuyen::orderby('ten_qh')->get();
        $xa = XaPhuong::orderby('ten_xa')->get();

        $user = $this->user->find($id);
        $dc = explode(', ', $user->dia_chi);
        return view('backend.nguoidung.capnhathoso', compact('user', 'dc', 'tinh_tp', 'huyen', 'xa'));
    }

    public function posthoso(Request $request, $id)
    {
        if ($request->has('ho_ten') && $request->has('sdt')) {
            $request->validate([
                'ho_ten' => 'required|max:191|unique:users',
                'sdt' => 'required|unique:users',
                'dia_chi' => 'required',
            ], [
                'ho_ten.required' => 'Hãy nhập tên người dùng',
                'ho_ten.max' => 'Tên người dùng quá dài',
                'ho_ten.unique' => 'Tên người dùng đã được sử dụng',
                'sdt.required' => 'Hãy nhập số điện thoại',
                'sdt.unique' => 'Số điện thoại đã được sử dụng',
                'dia_chi.required' => 'Hãy nhập địa chỉ',

            ]);
        } elseif ($request->has('ho_ten')) {
            $request->validate([
                'ho_ten' => 'required|max:191|unique:users',
                'dia_chi' => 'required',
            ], [
                'ho_ten.required' => 'Hãy nhập tên người dùng',
                'ho_ten.max' => 'Tên người dùng quá dài',
                'ho_ten.unique' => 'Tên người dùng đã được sử dụng',
                'dia_chi.required' => 'Hãy nhập địa chỉ',

            ]);
        } elseif ($request->has('sdt')) {
            $request->validate([
                'ho_ten' => 'max:191',
                'sdt' => 'required|unique:users',
                'dia_chi' => 'required|max:191',
            ], [
                'ho_ten.max' => 'Tên người dùng quá dài',
                'sdt.required' => 'Hãy nhập số điện thoại',
                'sdt.unique' => 'Số điện thoại đã được sử dụng',
                'dia_chi.required' => 'Hãy nhập địa chỉ',

            ]);
        } else {
            $request->validate([
                'ten_nd' => 'max:191',
                'dia_chi' => 'required|max:191',
            ], [
                'ten_nd.max' => 'Tên người dùng quá dài',
                'dia_chi.required' => 'Hãy nhập địa chỉ',
            ]);
        }

        try {
            DB::beginTransaction();
            if ($request->has('ten_nd')) $ten = $request->ten_nd;
            else $ten = $request->ho_ten;

            if ($request->has('sodt')) $sdt = $request->sodt;
            else $sdt = $request->sdt;

            $xa = XaPhuong::where('id', $request->opt_Xa)->first();
            $huyen = QuanHuyen::where('id', $request->opt_Huyen)->first();
            $tinh = TinhThanhPho::where('id', $request->opt_Tinh)->first();

            $diachi = $request->dia_chi . ', ' . $xa->ten_xa . ', ' . $huyen->ten_qh . ', ' . $tinh->ten_tp;

            $user = $this->user->find($id);
            $user->id = $request->id;
            $user->ho_ten = trim($ten);
            $user->sdt = trim($sdt);
            $user->dia_chi = trim($diachi);
            $user->save();
            DB::commit();
            Alert::success('Thành công', 'Cập nhật hồ sơ thành công');
            return redirect()->route('admin.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            Alert::error('Thất bại', 'Cập nhật hồ sơ thất bại');
            return redirect()->route('nguoidung.gethoso', ['id' => $id]);
        }
    }

    public function getdoimatkhau($id)
    {
        $user = $this->user->find($id);
        return view('backend.nguoidung.doimatkhau', compact('user'));
    }

    public function postdoimatkhau(Request $request,  $id)
    {
        $request->validate([
            'password' => 'required',
            'password_new' => 'required|different:password',
            'password_confirm' => 'required|same:password_new',
        ], [
            'password.required' => 'Hãy nhập mật khẩu cũ',
            'password_new.required' => 'Hãy nhập mật khẩu mới',
            'password_new.different' => 'Mật khẩu mới phải khác mật khẩu cũ',
            'password_confirm.required' => 'Hãy nhập lại mật khẩu mới',
            'password_confirm.same' => 'Nhập lại mật khẩu và mật khẩu mới phải giống nhau',
        ]);
        try {
            DB::beginTransaction();
            $u = $this->user->find($id);
            if (Hash::check($request->password, $u->password)) {
                $u->update([
                    'password' => Hash::make($request->password_new)
                ]);
                DB::commit();
                Alert::success('Thành công', 'Mật khẩu đã được thay đổi');
                return redirect()->route('nguoidung.index');
            } else {
                Alert::error('Thất bại', 'Mật khẩu cũ không đúng');
                return redirect()->route('nguoidung.getdoimatkhau', ['id' => $id]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            Alert::error('Thất bại', 'Đổi mật khẩu thất bại');
            return redirect()->route('nguoidung.getdoimatkhau', ['id' => $id]);
        }
    }

    public function getcapnhatquyen($id)
    {
        $id_sua = $id;
        $capnhatquyen = 'capnhatquyen';
        $page = 5;
        $users = $this->user::orderBy('ho_ten')->paginate($page);
        return view('backend.nguoidung.home', compact("users", "capnhatquyen", "id_sua"))->with('i', (request()->input('page', 1) - 1) * $page);
    }

    public function postcapnhatquyen(Request $request,  $id)
    {
        try {
            DB::beginTransaction();
            $u = $this->user->find($id);
            $u->id = $request->id;
            $u->quyen = $request->opt_quyen;
            $u->save();
            DB::commit();
            Alert::success('Thành công', 'Cập nhật quyền thành công');
            return redirect()->route('nguoidung.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            Alert::error('Thất bại', 'Cập nhật quyền thất bại');
            return redirect()->route('nguoidung.index');
        }
    }

    public function trangthai(Request $request,  $id)
    {
        try {
            DB::beginTransaction();
            $u = $this->user->find($id);
            $u->id = $request->id;
            $u->trang_thai = $request->khoa;
            $u->save();
            DB::commit();
            Alert::success('Thành công', 'Thay đổi trạng thái tài khoản thành công');
            return redirect()->route('nguoidung.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            Alert::error('Thất bại', 'Thay đổi trạng thái tài khoản thất bại');
            return redirect()->route('nguoidung.index');
        }
    }

    public function xoa($id)
    {
        return $this->deleteModelTrait($id, $this->user);
    }

    public function timkiem(Request $request)
    {
        if ($request->ajax()) {
            $id_sua = '0';
            $capnhatquyen = '';
            $page = 5;
            $timkiem =  $this->user->where('ho_ten', 'LIKE', '%' . $request->timkiem_nd . '%')->orderby('ho_ten')->paginate($page);
            if ($timkiem->count() > 0) {
                $kq = '';
                $i = (request()->input('page', 1) - 1) * $page;
                foreach ($timkiem as $u) {
                    $kq .= '<tr>
                        <td>' . ++$i . '</td>
                        <td >' . $u->ho_ten . '</td>
                        <td >' . $u->email . '</td>
                        <td >' . $u->quyen . '</td>
                        <td>' . Carbon::createFromFormat("Y-m-d H:i:s", $u->updated_at)->format("H:i:s d/m/Y") . '</td>';
                    if ($u->trang_thai == 1)
                        $kq .= '<td> Kích hoạt </td>';
                    else
                        $kq .= '<td> Khóa </td>';
                    if ($u->quyen != "Quản trị") {
                        if ($u->trang_thai == 1) {
                            $kq .= '<td> 
                                    <form action="' . route("nguoidung.trangthai", ["id" => $u->id]) . '" method="post">
                                        @csrf
                                        <input type="hidden" name = "khoa" value="0">
                                        <button type="submit" class="btn btn-danger action_edit" >Khóa</button>
                                    </form>
                                </td>';
                        } else {
                            $kq .= '
                                    <td>
                                        <form action="' . route("nguoidung.trangthai", ["id" => $u->id]) . '" method="post">
                                        @csrf
                                            <input type="hidden" name = "khoa" value="1">
                                            <button type="submit" class="btn btn-primary">Kích hoạt</button>
                                        </form>
                                    </td>';
                        }
                    } else
                        $kq .= '<td></td>';

                    if ($id_sua == $u->id && $capnhatquyen == "capnhatquyen") {
                        $kq .= '<td>
                            <form action="' . route("nguoidung.postcapnhatquyen", ["id" => $id_sua]) . '" method="post">
                                @csrf
                                <select
                                    id="opt_quyen"
                                    name="opt_quyen"
                                >
                                    <option';
                        if ($u->quyen == "Quản trị") $kq .= '" selected"';
                        else $kq .= '" "';
                        $kq .= 'value="Quản trị">Quản trị</option>
                                    <option';
                        if ($u->quyen == "Nhân viên") $kq .= '" selected"';
                        else $kq .= '" "';
                        $kq .= 'value="Nhân viên">Nhân viên</option>
                                    <option';
                        if ($u->quyen == "Khách hàng") $kq .= '" selected"';
                        else $kq .= '" "';
                        $kq .= ' value="Khách hàng">Khách hàng</option>
                                </select>
                                <button style="
                                    min-width: max-content;
                                    padding: 3px 12px;
                                    margin: 3px;" 
                                    type="submit" class="btn btn-warning"> Cập nhật
                                </button>
                            </form>                              
                        </td>';
                    } else {
                        $kq .= '<td>
                                <a                                         
                                    style="
                                        min-width: max-content;
                                        padding: 3px 12px;
                                        margin: 3px;
                                    "
                                    class="btn btn-warning"
                                    href="' . route("nguoidung.getcapnhatquyen", ["id" => $u->id]) . '"
                                >
                                    
                                    Cập nhật 
                                </a>                       
                            </td>';
                    }
                    $kq .= '
                        <td>
                           <a
                                style="
                                    min-width: 110px;
                                    padding: 3px 12px;
                                    margin: 3px;
                                "
                                class="btn btn-danger action_del"
                                href=""
                                data-url="' . route("nguoidung.xoa", ["id" => $u->id]) . '"
                                    >
                                        Xóa
                            </a>
                        </td>
                    </tr>';
                }
                return Response($kq);
            } else
                return response()->json(['status' => 'Không tìm thấy',]);
        }
    }
    // Kết thúc trang admin 

    //Bắt đầu trang user
    public function gethosoUser(Request $request, $id)
    {
        $dt = $this->cauhinh->where('cau_hinh_key', 'Điện thoại')->first();
        $fb = $this->cauhinh->where('cau_hinh_key', 'Facebook')->first();
        $email = $this->cauhinh->where('cau_hinh_key', 'Email')->first();

        //SEO
        $meta_keyword = '';
        $meta_image = '';
        $meta_description = '';
        $meta_title = '';
        $url_canonical = $request->url();

        $tinh_tp = TinhThanhPho::orderby('ten_tp')->get();
        $huyen = QuanHuyen::orderby('ten_qh')->get();
        $xa = XaPhuong::orderby('ten_xa')->get();

        $user = $this->user->find($id);
        $dc = explode(', ', $user->dia_chi);

        $dm =  $this->dmuc->orderby('ten_dm', 'asc')->get();
        $th = $this->thuonghieu->orderby('ten_thuong_hieu')->get();
        return view('auth.hosonguoidung', compact('user', 'dc', 'tinh_tp', 'huyen', 'xa', 'dm', 'th', 'url_canonical', 'meta_keyword', 'meta_image', 'meta_description', 'meta_title', 'dt', 'fb', 'email'));
    }

    public function posthosoUser(Request $request, $id)
    {
        if ($request->has('ho_ten') && $request->has('sdt')) {
            $request->validate([
                'ho_ten' => 'required|max:191|unique:users',
                'sdt' => 'required|unique:users',
                'dia_chi' => 'required',
            ], [
                'ho_ten.required' => 'Hãy nhập tên người dùng',
                'ho_ten.max' => 'Tên người dùng quá dài',
                'ho_ten.unique' => 'Tên người dùng đã được sử dụng',
                'sdt.required' => 'Hãy nhập số điện thoại',
                'sdt.unique' => 'Số điện thoại đã được sử dụng',
                'dia_chi.required' => 'Hãy nhập địa chỉ',

            ]);
        } elseif ($request->has('ho_ten')) {
            $request->validate([
                'ho_ten' => 'required|max:191|unique:users',
                'dia_chi' => 'required',
            ], [
                'ho_ten.required' => 'Hãy nhập tên người dùng',
                'ho_ten.max' => 'Tên người dùng quá dài',
                'ho_ten.unique' => 'Tên người dùng đã được sử dụng',
                'dia_chi.required' => 'Hãy nhập địa chỉ',

            ]);
        } elseif ($request->has('sdt')) {
            $request->validate([
                'ho_ten' => 'max:191',
                'sdt' => 'required|unique:users',
                'dia_chi' => 'required|max:191',
            ], [
                'ho_ten.max' => 'Tên người dùng quá dài',
                'sdt.required' => 'Hãy nhập số điện thoại',
                'sdt.unique' => 'Số điện thoại đã được sử dụng',
                'dia_chi.required' => 'Hãy nhập địa chỉ',

            ]);
        } else {
            $request->validate([
                'ten_nd' => 'max:191',
                'dia_chi' => 'required|max:191',
            ], [
                'ten_nd.max' => 'Tên người dùng quá dài',
                'dia_chi.required' => 'Hãy nhập địa chỉ',
            ]);
        }

        try {
            DB::beginTransaction();
            if ($request->has('ten_nd')) $ten = $request->ten_nd;
            else $ten = $request->ho_ten;

            if ($request->has('sodt')) $sdt = $request->sodt;
            else $sdt = $request->sdt;

            $xa = XaPhuong::where('id', $request->opt_Xa)->first();
            $huyen = QuanHuyen::where('id', $request->opt_Huyen)->first();
            $tinh = TinhThanhPho::where('id', $request->opt_Tinh)->first();

            $diachi = $request->dia_chi . ', ' . $xa->ten_xa . ', ' . $huyen->ten_qh . ', ' . $tinh->ten_tp;

            $user = $this->user->find($id);
            $user->id = $request->id;
            $user->ho_ten = trim($ten);
            $user->sdt = trim($sdt);
            $user->dia_chi = trim($diachi);
            $user->save();
            DB::commit();
            session()->flash('success', 'Cập nhật hồ sơ thành công');
            return redirect()->route('nguoidung.posthosoUser', ['id' => $id]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            session()->flash('err', 'Cập nhật hồ sơ thất bại');
            return redirect()->route('nguoidung.posthosoUser', ['id' => $id]);
        }
    }

    public function getdoimatkhauUser(Request $request, $id)
    {
        $dt = $this->cauhinh->where('cau_hinh_key', 'Điện thoại')->first();
        $fb = $this->cauhinh->where('cau_hinh_key', 'Facebook')->first();
        $email = $this->cauhinh->where('cau_hinh_key', 'Email')->first();

        //SEO
        $meta_keyword = '';
        $meta_image = '';
        $meta_description = '';
        $meta_title = '';
        $url_canonical = $request->url();

        $dm =  $this->dmuc->orderby('ten_dm', 'asc')->get();
        $th = $this->thuonghieu->orderby('ten_thuong_hieu')->get();
        $user = $this->user->find($id);
        return view('auth.doimatkhaunguoidung', compact('user', 'dm', 'th', 'url_canonical', 'meta_keyword', 'meta_image', 'meta_description', 'meta_title', 'dt', 'fb', 'email'));
    }

    public function postdoimatkhauUser(Request $request,  $id)
    {
        $request->validate([
            'password' => 'required',
            'password_new' => 'required|different:password|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'password_confirm' => 'required|same:password_new',
        ], [
            'password.required' => 'Hãy nhập mật khẩu cũ',
            'password_new.required' => 'Hãy nhập mật khẩu mới',
            'password_new.different' => 'Mật khẩu mới phải khác mật khẩu cũ',
            'password_new.regex' => 'Mật khẩu có ít nhất 1 số, 1 chữ hoa, 1 chữ thường và 1 ký tự đặc biệt',
            'password_confirm.required' => 'Hãy nhập lại mật khẩu mới',
            'password_confirm.same' => 'Nhập lại mật khẩu và mật khẩu mới phải giống nhau',
        ]);
        try {
            DB::beginTransaction();
            $u = $this->user->find($id);
            if (Hash::check($request->password, $u->password)) {
                $u->update([
                    'password' => Hash::make($request->password_new)
                ]);
                DB::commit();
                session()->flash('success', 'Mật khẩu đã được thay đổi');
                return redirect()->route('nguoidung.getdoimatkhauUser', ['id' => $id]);
            } else {
                session()->flash('err_pw_cu', 'Mật khẩu cũ không đúng');
                return redirect()->route('nguoidung.getdoimatkhauUser', ['id' => $id]);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            session()->flash('err', 'Đổi mật khẩu thất bại');
            return redirect()->route('nguoidung.getdoimatkhauUser', ['id' => $id]);
        }
    }
}