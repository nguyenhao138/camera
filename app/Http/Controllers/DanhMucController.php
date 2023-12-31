<?php

namespace App\Http\Controllers;

use App\Models\DanhMuc;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Components\Traits\DeleteModelTrait;
use App\Components\Traits\RestoreModelTrait;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\SanPham;
use App\Models\ThuongHieu;
use App\Models\CauHinh;
use App\Models\DonHang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class DanhMucController extends Controller
{
    use DeleteModelTrait, RestoreModelTrait;
    private $dmuc, $sanpham, $thuonghieu, $cauhinh, $dhang;
    public function __construct(DanhMuc $dmuc, SanPham $sanpham, ThuongHieu $thuonghieu, CauHinh $cauhinh, DonHang $dhang)
    {
        $this->dmuc = $dmuc;
        $this->sanpham = $sanpham;
        $this->thuonghieu = $thuonghieu;
        $this->cauhinh = $cauhinh;
        $this->dhang = $dhang;
    }

    // Bắt đầu trang admin

    public function index(Request $request)
    {
        if (Gate::allows('quyen', "Khách hàng")) {
            return redirect()->route('home.index');
        }
        $page = 5;
        $dm = $this->dmuc::where('trang_thai', 1)->orderBy('ten_dm')->paginate($page)->appends($request->query());
        $dh_moi =  $this->dhang->where('trang_thai', "Đang chờ xử lý")->count();
        $dm_daxoa = $this->dmuc::where('trang_thai', 0)->get();
        return view('backend.danhmuc.home', compact('dm', 'dh_moi', 'dm_daxoa'))->with('i', (request()->input('page', 1) - 1) * $page);
    }

    public function postThem(Request $request)
    {
        $request->validate([
            'ten_dm' => 'required|max:191|unique:danh_mucs',
        ], [
            'ten_dm.required' => 'Hãy nhập danh mục',
            'ten_dm.max' => 'Danh mục quá dài',
            'ten_dm.unique' => 'Danh mục đã tồn tại',
        ]);
        try {

            DB::beginTransaction();
            $this->dmuc->firstOrCreate([
                'ten_dm' => trim($request->ten_dm),
                'slug' => Str::slug($request->ten_dm, '-'),
            ]);
            DB::commit();
            Alert::success('Thành công', 'Thêm danh mục thành công');
            return redirect()->route('danhmuc.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            Alert::error('Thất bại', 'Thêm danh mục thất bại');
            return redirect()->route('danhmuc.index');
        }
    }

    public function getSua($id)
    {
        if (Gate::allows('quyen', "Khách hàng")) {
            return redirect()->route('home.index');
        }
        $dm = $this->dmuc->find($id);
        $dh_moi =  $this->dhang->where('trang_thai', "Đang chờ xử lý")->count();
        return view('backend.danhmuc.sua', compact('dm', 'dh_moi'));
    }

    public function postSua(Request $request, $id)
    {
        if ($request->has('ten_dm')) {
            $request->validate([
                'ten_dm' => 'required|max:191|unique:danh_mucs',
            ], [
                'ten_dm.required' => 'Hãy nhập danh mục',
                'ten_dm.max' => 'Danh mục quá dài',
                'ten_dm.unique' => 'Danh mục đã tồn tại',
            ]);
        }
        try {
            DB::beginTransaction();
            $dm = $this->dmuc->find($id);
            if ($request->has('ten_dm2')) $ten_dm = trim($request->ten_dm2);
            else $ten_dm = $request->ten_dm;
            $dm->id = $request->id;
            $dm->ten_dm = $ten_dm;
            $dm->slug = Str::slug($request->ten_dm, '-');
            $dm->save();
            DB::commit();
            Alert::success('Thành công', 'Cập nhật danh mục thành công');
            return redirect()->route('danhmuc.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Message: ' . $exception->getMessage() . ' --- Line : ' . $exception->getLine());
            Alert::error('Thất bại', 'Cập nhật danh mục thất bại');
            return redirect()->route('danhmuc.getSua', ['id' => $id]);
        }
    }

    public function xoa($id)
    {
        if (Gate::allows('quyen', "Khách hàng")) {
            return redirect()->route('home.index');
        }
        return $this->deleteModelTrait($id, $this->dmuc);
    }

    public function daxoa(Request $request)
    {
        if (Gate::allows('quyen', "Khách hàng")) {
            return redirect()->route('home.index');
        }
        if (Gate::allows('quyen', "Nhân viên")) {
            return redirect()->route('admin.index');
        }
        $page = 5;
        $dm = $this->dmuc::where('trang_thai', 0)->orderBy('ten_dm')->paginate($page)->appends($request->query());
        $dh_moi =  $this->dhang->where('trang_thai', "Đang chờ xử lý")->count();
        return view('backend.danhmuc.deleted', compact('dm', 'dh_moi'))->with('i', (request()->input('page', 1) - 1) * $page);
    }
    public function restore($id)
    {
        if (Gate::allows('quyen', "Khách hàng")) {
            return redirect()->route('home.index');
        }
        return $this->restoreModelTrait($id, $this->dmuc);
    }

    public function timkiem(Request $request)
    {
        if (Gate::allows('quyen', "Khách hàng")) {
            return redirect()->route('home.index');
        }
        if ($request->ajax()) {
            $page = 5;
            $timkiem =  $this->dmuc->where('trang_thai', 1)->where('ten_dm', 'LIKE', '%' . $request->timkiem_dm . '%')->orderby('ten_dm')->paginate($page)->appends($request->query());
            if ($timkiem->count() > 0) {
                $kq = '';
                $i = (request()->input('page', 1) - 1) * $page;
                foreach ($timkiem as $d) {
                    $kq .= '<tr>
                        <td>' . ++$i . '</td>
                        <td style="text-align: left">' . $d->ten_dm . '</td>
                        <td>' . Carbon::createFromFormat("Y-m-d H:i:s", $d->updated_at)->format("H:i:s d/m/Y") . '</td>
                        <td>
                            <a
                                style="
                                    width: 88px;
                                    padding: 3px 10px;
                                    margin: 3px;
                                "
                                class="btn btn-warning"
                                href="' . route("danhmuc.getSua", ["id" => $d->id]) . '"
                            >
                                Cập nhật
                            </a>';
                    if (auth()->check() && auth()->user()->quyen == "Quản trị") {
                        $kq .= '<a
                                style="
                                    width: 88px;
                                    padding: 3px 10px;
                                    margin: 3px;
                                "
                                class="btn btn-danger action_del"
                                href=""
                                data-url="' . route("danhmuc.xoa", ["id" => $d->id]) . '"
                            >
                                Xóa
                            </a>';
                    }
                    $kq .= '
                        </td>
                    </tr>';
                }
                return Response($kq);
            } else
                return response()->json(['status' => 'Không tìm thấy',]);
        }
    }
    // Kết thúc trang admin

    // Bắt đầu trang người dùng
    public function getDanhMucSanPham(Request $request, $slug, $id_dm)
    {
        $dt = $this->cauhinh->where('ten', 'Điện thoại')->first();
        $fb = $this->cauhinh->where('ten', 'Facebook')->first();
        $email = $this->cauhinh->where('ten', 'Email')->first();
        $dc = $this->cauhinh->where('ten', 'Địa chỉ')->first();

        //SEO
        $meta_keyword = '';
        $meta_image = '';
        $meta_description = '';
        $meta_title = '';
        $url_canonical = $request->url();

        if ($request->hienthi)
            $hienthi = $request->hienthi;
        else
            $hienthi = 6;

        $sx_sp = $request->sx_sp;
        $loc_gia = $request->gia;

        $dm =  $this->dmuc->where('trang_thai', 1)->orderby('ten_dm')->get();
        $th = $this->thuonghieu::where('trang_thai', 1)->get();

        $spham = $this->sanpham->where('trang_thai', 1)->whereIn('thuong_hieu_id', $th->pluck('id'))->whereIn('dm_id', $dm->pluck('id'))->where('dm_id', $id_dm);
        switch ($sx_sp) {
            case 'a_z':
                $spham->orderby('ten_sp')->get();
                break;
            case 'z_a':
                $spham->orderby('ten_sp', 'desc')->get();
                break;
            case 'thap_cao':
                $spham->orderby('gia_ban')->get();
                break;
            case 'cao_thap':
                $spham->orderby('gia_ban', 'desc')->get();
                break;
            default:
                $spham->get();
        }
        switch ($loc_gia) {
            case '1':
                $spham->where('gia_ban', '<', '1000000')->orderby('ten_sp')->get();
                break;
            case '1-3':
                $spham->whereBetween('gia_ban', ['1000000', '3000000'])->orderby('ten_sp', 'desc')->get();
                break;
            case '3-5':
                $spham->whereBetween('gia_ban', ['3000000', '5000000'])->orderby('gia_ban')->get();
                break;
            case '5-8':
                $spham->whereBetween('gia_ban', ['5000000', '8000000'])->orderby('gia_ban', 'desc')->get();
                break;
            case '8-10':
                $spham->whereBetween('gia_ban', ['8000000', '10000000'])->orderby('gia_ban', 'desc')->get();
                break;
            case '10':
                $spham->where('gia_ban', '>', '10000000')->orderby('gia_ban', 'desc')->get();
                break;
            default:
                $spham->get();
        }

        $sp = $spham->paginate($hienthi)->appends($request->query());

        $dm =  $this->dmuc->where('trang_thai', 1)->orderby('ten_dm')->get();
        $ten_dm = $this->dmuc->where('trang_thai', 1)->where('id', $id_dm)->limit(1)->get();
        if (count($sp) > 0) {
            foreach ($sp as $value)
                $id_th[] = $value->thuong_hieu_id;
            $th_sp = $this->thuonghieu->whereIn('id', $id_th)->distinct()->get();
        } else {
            $th_sp = [];
        }

        return view('frontend.danhmuc_sanpham', compact('dm', 'sp', 'ten_dm', 'th_sp', 'url_canonical', 'meta_keyword', 'meta_image', 'meta_description', 'meta_title', 'dc', 'dt', 'fb', 'email'));
    }
    // Kết thúc trang người dùng
}
