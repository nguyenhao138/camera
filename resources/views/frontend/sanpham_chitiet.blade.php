@extends('layouts.user') @section('title')
<title>Sản phẩm chi tiết</title>
@endsection @section('css')

<link
    href="{{
        asset('frontend/assets_theme/plugins/uniform/css/uniform.default.css')
    }}"
    rel="stylesheet"
    type="text/css"
/>
<link
    href="{{ asset('frontend/assets_theme/plugins/smoothness/jquery-ui.css') }}"
    rel="stylesheet"
    type="text/css"
/>

<link
    rel="stylesheet"
    href="{{ asset('frontend/assets_theme/plugins/carousel/carousel.css') }}"
/>
<!-- for slider-range -->
<link
    href="{{ asset('frontend/assets_theme/plugins/rateit/src/rateit.css') }}"
    rel="stylesheet"
    type="text/css"
/>
@endsection @section('content')
<div class="row margin-bottom-40" style="margin-top: 40px">
    <div class="col-md-12 col-sm-7">
        <div class="product-page">
            <div class="row">
                @foreach($sp_chitiet as $spct)
                <div class="col-md-6 col-sm-6">
                    <div class="product-main-image">
                        <img
                            src="{{$spct->hinh_anh_chinh}}"
                            class="img-responsive"
                            data-BigImgsrc="{{$spct->hinh_anh_chinh}}"
                        />
                    </div>
                    <div class="product-other-images">
                        @foreach($spct->HinhAnh as $hinh)
                        <a
                            href="{{$hinh->hinh_anh}}"
                            class="fancybox-button"
                            rel="photos-lib"
                        >
                            <img src="{{$hinh->hinh_anh}}" />
                        </a>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <h1>{{$spct->ten_sp}}</h1>
                    <div class="price-availability-block clearfix">
                        <div class="price">
                            <strong>
                                {{number_format(($spct->gia_ban-($spct->gia_ban*$spct->giam_gia/100)),0,',','.')
                                }}
                                đ
                            </strong>
                            @if($spct->giam_gia!=0)
                            <span style="font-size: 18px">
                                <del>
                                    {{number_format(($spct->gia_ban),0,',','.')
                                    }}
                                    đ
                                </del>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div
                        class="availability"
                        style="margin-bottom: 8px; font-size: 18px"
                    >
                        <b> Tình trạng:</b> @if($spct->ton>0)
                        <strong style="color: rgb(41, 218, 100)">
                            Còn hàng
                        </strong>
                        @else
                        <strong style="color: red">Hết hàng</strong>
                        @endif
                    </div>
                    <div
                        class="description"
                        style="margin-bottom: 8px; font-size: 18px"
                    >
                        <b> Danh mục:</b>
                        <span> {{$spct->DanhMuc->ten_dm}} </span>
                    </div>
                    <div
                        class="description"
                        style="margin-bottom: 8px; font-size: 18px"
                    >
                        <b> Thương hiệu:</b>
                        <span>
                            {{$spct->ThuongHieu->ten_thuong_hieu}}
                        </span>
                    </div>
                    @if($spct->ton>0)
                    <div class="product-page-cart" style="margin-top: 30px">
                        <form
                            action="{{ route('giohang.them_giohang') }}"
                            method="post"
                        >
                            @csrf
                            <input
                                type="hidden"
                                name="id_sp"
                                value="{{$spct->id}}"
                            />
                            <input
                                type="hidden"
                                name="gia"
                                value="{{number_format(($spct->gia_ban-($spct->gia_ban*$spct->giam_gia/100)),0,',','.')}}"
                            />
                            <div class="product-quantity">
                                <input
                                    id="product-quantity"
                                    name="num_so_luong"
                                    type="number"
                                    min="1"
                                    value="1"
                                    onchange="SoLuongMinMax(this)"
                                    max="{{$spct->ton}}"
                                    readonly
                                    class="form-control input-sm"
                                />
                            </div>
                            <button class="btn btn-primary" type="submit">
                                Thêm vào giỏ
                            </button>
                        </form>
                    </div>
                    @endif
                    <div class="review">
                        <input
                            type="range"
                            value="4"
                            step="0.25"
                            id="backing4"
                        />
                        <div
                            class="rateit"
                            data-rateit-backingfld="#backing4"
                            data-rateit-resetable="false"
                            data-rateit-ispreset="true"
                            data-rateit-min="0"
                            data-rateit-max="5"
                        ></div>
                        <a href="javascript:;">7 reviews</a
                        >&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript:;"
                            >Write a review</a
                        >
                    </div>
                    <ul class="social-icons">
                        <li>
                            <a
                                class="facebook"
                                data-original-title="facebook"
                                href="javascript:;"
                            ></a>
                        </li>
                    </ul>
                </div>

                <div class="product-page-content" style="font-size: 18px">
                    <ul id="myTab" class="nav nav-tabs">
                        <li class="active">
                            <a href="#Mo_ta" data-toggle="tab">
                                <span>Mô tả</span>
                            </a>
                        </li>
                        <li>
                            <a href="#Tinh_nang" data-toggle="tab">
                                <span>Tính năng</span>
                            </a>
                        </li>
                    </ul>
                    <div id="myTabContent" class="tab-content">
                        <div class="tab-pane fade in active" id="Mo_ta">
                            <p>{!!$spct->mo_ta!!}</p>
                        </div>
                        <div class="tab-pane fade" id="Tinh_nang">
                            {!!$spct->tinh_nang!!}
                        </div>
                    </div>
                    <!-- Sản phẩm liên quan -->
                    <div class="row margin-bottom-40">
                        <div class="sale-product">
                            <h2><b>Sản phẩm liên quan</b></h2>
                            <div
                                id="carouselExampleControls"
                                class="carousel slide"
                                data-ride="carousel"
                            >
                                <div class="carousel-inner">
                                    @foreach($sp_lienquan as $key => $lienquan)
                                    <div
                                        class="item {{
                                            $key == 0 ? 'active' : ''
                                        }} "
                                    >
                                        <div
                                            class="col-sm-3"
                                            style="margin-top: 6px"
                                        >
                                            <div class="product-item">
                                                <div class="pi-img-wrapper">
                                                    <img
                                                        src="{{$lienquan->hinh_anh_chinh}}"
                                                        class="img-responsive"
                                                    />
                                                    <div>
                                                        <a
                                                            href="{{$lienquan->hinh_anh_chinh}}"
                                                            class="btn btn-default fancybox-button"
                                                        >
                                                            Phóng to
                                                        </a>
                                                        <a
                                                            href="{{route('sanpham.chitiet',[$lienquan->id])}}"
                                                            class="btn btn-default fancybox-fast-view"
                                                        >
                                                            Chi tiết
                                                        </a>
                                                    </div>
                                                </div>
                                                <h3>
                                                    <a
                                                        href="{{route('sanpham.chitiet',[$lienquan->id])}}"
                                                    >
                                                        <b>
                                                            {{$lienquan->ten_sp}}
                                                        </b>
                                                    </a>
                                                </h3>
                                                <div class="pi-price">
                                                    {{number_format(($lienquan->gia_ban-($lienquan->gia_ban*$lienquan->giam_gia/100)),0,',','.')
                                                    }}
                                                    đ
                                                </div>
                                                <a
                                                    href="javascript:;"
                                                    class="btn add2cart"
                                                    style="
                                                        background-color: rgba(
                                                            204,
                                                            204,
                                                            204,
                                                            0.5
                                                        );
                                                    "
                                                >
                                                    Thêm vào giỏ
                                                </a>

                                                @if($lienquan->giam_gia !=0)
                                                <div
                                                    class="sticker sticker-sale"
                                                ></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <a
                                    class="carousel-control-prev"
                                    href="#carouselExampleControls"
                                    role="button"
                                    data-slide="prev"
                                >
                                    <span
                                        class="carousel-control-prev-icon"
                                        aria-hidden="true"
                                    ></span>
                                    <span class="sr-only"> Previous </span>
                                </a>
                                <a
                                    class="carousel-control-next"
                                    href="#carouselExampleControls"
                                    role="button"
                                    data-slide="next"
                                >
                                    <span
                                        class="carousel-control-next-icon"
                                        aria-hidden="true"
                                    ></span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- BEGIN FORM-->
                    <form action="#" class="reviews-form" role="form">
                        <h2>Write a review</h2>
                        <div class="form-group">
                            <label for="name"
                                >Name

                                <span class="require">*</span></label
                            >
                            <input type="text" class="form-control" id="name" />
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                type="text"
                                class="form-control"
                                id="email"
                            />
                        </div>
                        <div class="form-group">
                            <label for="review"
                                >Review <span class="require">*</span></label
                            >
                            <textarea
                                class="form-control"
                                rows="8"
                                id="review"
                            ></textarea>
                        </div>
                        <div class="form-group">
                            <label for="email">Rating</label>
                            <input
                                type="range"
                                value="4"
                                step="0.25"
                                id="backing5"
                            />
                            <div
                                class="rateit"
                                data-rateit-backingfld="#backing5"
                                data-rateit-resetable="false"
                                data-rateit-ispreset="true"
                                data-rateit-min="0"
                                data-rateit-max="5"
                            ></div>
                        </div>
                        <div class="padding-top-20">
                            <button type="submit" class="btn btn-primary">
                                Send
                            </button>
                        </div>
                    </form>
                    <!-- END FORM-->
                </div>

                @if($spct->giam_gia !=0)
                <div class="sticker sticker-sale"></div>
                @endif @endforeach
            </div>
        </div>
    </div>
</div>

@endsection @section('js')
<script
    src="{{
        asset('frontend/assets_theme/plugins/uniform/jquery.uniform.min.js')
    }}"
    type="text/javascript"
></script>
<script
    src="{{
        asset('frontend/assets_theme/plugins/rateit/src/jquery.rateit.js')
    }}"
    type="text/javascript"
></script>
<script src="{{
        asset('frontend/assets_theme/plugins/carousel/carousel.js')
    }}"></script>
<script>
    function SoLuongMinMax(el) {
        if (el.value != "") {
            if (parseInt(el.value) < parseInt(el.min)) {
                el.value = el.min;
            }
            if (parseInt(el.value) > parseInt(el.max)) {
                el.value = el.max;
            }
        }
    }
</script>
@endsection