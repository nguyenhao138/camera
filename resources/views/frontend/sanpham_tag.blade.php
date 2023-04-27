@extends('layouts.user') @section('title')
<title>Sản phẩm</title>
@endsection @section('content')

<div class="row margin-bottom-40" style="margin-top: 40px">
    @include('view-page.user.sidebar_menu')
    <div class="col-md-9 col-sm-7">
        <div class="row list-view-sorting clearfix">
            <div class="col-md-7 col-sm-7">
                <h3>Sản phẩm cho tag {{$tags->ten_tag}}</h3>
            </div>
        </div>
        <!-- BEGIN PRODUCT LIST -->
        <div class="row product-list" style="min-height: 300px">
            <!-- PRODUCT ITEM START -->

            @if($tags->count()>0) @foreach($tags->SanPhamTag as $s)
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="product-item">
                    <div class="pi-img-wrapper">
                        <img
                            src="{{$s->hinh_anh_chinh}}"
                            class="img-responsive"
                        />
                        <div>
                            <a
                                href="{{$s->hinh_anh_chinh}}"
                                class="btn btn-default fancybox-button"
                            >
                                Phóng to
                            </a>
                            <a
                                href="{{route('sanpham.chitiet_sp',[$s->id])}}"
                                class="btn btn-default fancybox-fast-view"
                            >
                                Chi tiết
                            </a>
                        </div>
                    </div>
                    <h3>
                        <a href="{{route('sanpham.chitiet_sp',[$s->id])}}">
                            <b> {{$s->ten_sp}} </b>
                        </a>
                    </h3>
                    <div class="pi-price">
                        {{number_format(($s->gia_ban-($s->gia_ban*$s->giam_gia/100)),0,',','.')






                        }}đ
                    </div>
                    <div class="price pull-right">
                        <del>
                            <i
                                style="
                                    margin-left: 1em;
                                    height: 25px;
                                    line-height: 2;
                                    vertical-align: middle;
                                "
                            >
                                {{number_format($s->gia_ban,0,',','.')









                                }}đ
                            </i>
                        </del>
                    </div>
                    <div>
                        <p
                            class="btn btn-so-sanh"
                            onclick="ThemSoSanh('{{$s->id}}')"
                        >
                            So sánh
                        </p>
                        <form>
                            @csrf
                            <input
                                type="hidden"
                                id="id_sp"
                                value="{{$s->id}}"
                            />
                            <input
                                type="hidden"
                                id="tensp_{{$s->id}}"
                                value="{{$s->ten_sp}}"
                            />
                            <input
                                type="hidden"
                                id="hinhanh_{{$s->id}}"
                                value="{{$s->hinh_anh_chinh}}"
                            />
                            <input
                                type="hidden"
                                id="giaban_{{$s->id}}"
                                value="{{number_format(($s->gia_ban-($s->gia_ban*$s->giam_gia/100)),0,',','.')}}"
                            />
                            <input
                                type="hidden"
                                id="tinhnang_{{$s->id}}"
                                value="{{$s->tinh_nang}}"
                            />
                            <a
                                id="url_{{$s->id}}"
                                href="{{route('sanpham.chitiet_sp', [$s->id])}}"
                            ></a>
                        </form>
                        <form
                            action="{{ route('giohang.them_giohang') }}"
                            method="post"
                        >
                            @csrf
                            <input
                                type="hidden"
                                name="id_sp"
                                value="{{$s->id}}"
                            />
                            <input
                                type="hidden"
                                name="num_so_luong"
                                value="1"
                            />
                            <button class="btn btn-them-gio-hang" type="submit">
                                Thêm vào giỏ
                            </button>
                        </form>
                    </div>
                    @if($s->giam_gia !=0)
                    <div class="giamgia">
                        <span class="chu">GIẢM</span>
                        <span class="phantram">{{$s->giam_gia}}%</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            <!-- PRODUCT ITEM END -->
            @else
            <div>
                <h3>Không có sản phẩm</h3>
            </div>
            @endif
        </div>

        <!-- END PRODUCT LIST -->
    </div>
</div>
<!-- Modal so sánh sản phẩm -->
<div class="container">
    <div class="modal fade" id="modal-sosanh" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                    <h4 class="modal-title" id="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <table class="table" id="sosanh">
                        <thead>
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Hình ảnh</th>
                                <th>Tính năng</th>
                                <th>Chi tiết</th>
                                <th>Xoá</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-default"
                        data-dismiss="modal"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection @section('js')
<script src="{{ asset('frontend/js/jquery.min.js') }}"></script>
<script>
    $(document).ready(function (event) {
        $("form").submit(function () {
            var form = $(this);
            var actionUrl = form.attr("action");
            $.ajax({
                type: "POST",
                url: actionUrl,
                data: form.serialize(),
                success: function (data) {
                    if (data.status === "Thêm thành công") {
                        alert("Đã thêm vào giỏ hàng");
                        location.reload(false);
                    } else alert("Thêm vào giỏ hàng thất bại");
                },
            });
        });
    });
    XemSoSanh();
    function XemSoSanh() {
        if (localStorage.getItem("sosanh_sp") != null) {
            var data = JSON.parse(localStorage.getItem("sosanh_sp"));
            for (var i = 0; i < data.length; i++) {
                var id = data[i].id;
                var tensp = data[i].tensp;
                var hinhanh = data[i].hinhanh;
                var giaban = data[i].giaban;
                var tinhnang = data[i].tinhnang;
                var url = data[i].url;
                $("#sosanh")
                    .find("tbody")
                    .append(
                        `
                    <tr id="row_sosanh_` +
                            id +
                            `">
                        <td>` +
                            tensp +
                            `</td>
                        <td>` +
                            giaban +
                            `đ</td>
                        <td><img width="100px" src="` +
                            hinhanh +
                            `"/></td>
                        <td>` +
                            tinhnang +
                            `</td>
                        <td> <a style="text-decoration: none;" href="` +
                            url +
                            `"> Xem </a></td>
                            <td><a style="cursor:pointer; text-decoration: none;" onclick="XoaSoSanh(` +
                            id +
                            `)">Xoá</a></td>
                    </tr>
                `
                    );
            }
        }
    }

    function ThemSoSanh(id_sp) {
        $("#modal-title").text("So sánh tối đa 3 sản phẩm");
        var id = id_sp;
        var tensp = $("#tensp_" + id).val();
        var hinhanh = $("#hinhanh_" + id).val();
        var giaban = $("#giaban_" + id).val();
        var tinhnang = $("#tinhnang_" + id).val();
        var url = $("#url_" + id).attr("href");
        var newItems = {
            id: id,
            tensp: tensp,
            hinhanh: hinhanh,
            giaban: giaban,
            tinhnang: tinhnang,
            url: url,
        };
        console.log(newItems);

        if (localStorage.getItem("sosanh_sp") == null)
            localStorage.setItem("sosanh_sp", "[]");

        var ds_sosanh = JSON.parse(localStorage.getItem("sosanh_sp"));
        var kt_sosanh = $.grep(ds_sosanh, function (obj) {
            return obj.id == id;
        });

        if (kt_sosanh.length) {
        } else {
            if (ds_sosanh.length <= 2) {
                ds_sosanh.push(newItems);
                $("#sosanh")
                    .find("tbody")
                    .append(
                        `
                    <tr id="row_sosanh_` +
                            newItems.id +
                            `">
                        <td>` +
                            newItems.tensp +
                            `</td>
                        <td>` +
                            newItems.giaban +
                            `đ</td>
                        <td><img width="100px" src="` +
                            newItems.hinhanh +
                            `"/></td>
                        <td>` +
                            newItems.tinhnang +
                            `</td>
                        <td> <a style="text-decoration: none;" href="` +
                            newItems.url +
                            `"> Xem </a></td>
                        <td><a style="cursor:pointer; text-decoration: none;" onclick="XoaSoSanh(` +
                            newItems.id +
                            `)">Xoá</a></td>
                    </tr>
                `
                    );
            }
        }
        localStorage.setItem("sosanh_sp", JSON.stringify(ds_sosanh));
        $("#modal-sosanh").modal();
    }
    function XoaSoSanh(id) {
        if (localStorage.getItem("sosanh_sp") != null) {
            var data = JSON.parse(localStorage.getItem("sosanh_sp"));
            var index = data.findIndex((item) => item.id === id);
            data.splice(index, 1);
            localStorage.setItem("sosanh_sp", JSON.stringify(data));
            $("#row_sosanh_" + id).remove();
        }
    }
</script>
@endsection