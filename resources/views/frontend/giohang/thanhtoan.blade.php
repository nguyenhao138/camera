@extends('layouts.user') @section('title')
    <title>Giỏ hàng</title>
    @endsection @section('css')
    <link href="{{ asset('frontend/assets_theme/plugins/uniform/css/uniform.default.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('frontend/assets_theme/plugins/smoothness/jquery-ui.css') }}" rel="stylesheet" type="text/css" />


    <link rel="stylesheet" href="{{ asset('frontend/assets_theme/plugins/carousel/carousel.css') }}" />
    <!-- for slider-range -->
    <link href="{{ asset('frontend/assets_theme/plugins/rateit/src/rateit.css') }}" rel="stylesheet" type="text/css" />
    @endsection @section('content')
    <div class="row margin-bottom-40" style="margin-top: 40px">
        <div class="col-md-12 col-sm-12">
            <h3>Vui lòng nhập đầy đủ thông tin</h3>
            <div class="col-sm-6">
                @if (Session::has('error'))
                    <h5 style="color: red;  font-weight: bold">{{ Session::get('error') }}</h5>
                    <br>
                @elseif(Session::has('errorPaypal'))
                    <h5 style="color: red;  font-weight: bold">{{ Session::get('errorPaypal') }}</h5>
                    <br>
                @elseif(Session::has('errorVNPAY'))
                    <h5 style="color: red;  font-weight: bold">{{ Session::get('errorVNPAY') }}</h5>
                    <br>
                @endif
                <form method="post" action="{{ route('thanhtoan.postThanhToan') }}" id="myform">
                    @csrf
                    @if (Auth::check())
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="{{ Auth::user()->email ?? '' }}"
                                disabled />
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="ho_ten">Họ tên</label>
                        <input type="text" class="form-control" name="ho_ten" id="ho_ten"
                            value="{{ Auth::user()->ho_ten ?? '' }}" placeholder="Nhập họ tên" required />
                    </div>
                    <div class="form-group">
                        <label for="sdt">Số điện thoại</label>
                        <input type="text" class="form-control" name="sdt" id="sdt" maxlength="10"
                            value="{{ Auth::user()->sdt ?? '' }}" placeholder="Nhập số điện thoại"
                            onblur="kiemTraSDT(event)" required />
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ giao hàng</label>
                        @if ($dc[0] != '')
                            <div class="form-group col-md-12 col-sm-12 " style="padding-left: 10px;">
                                <div class="col-md-4 col-sm-4" style="padding-left: 0;">
                                    <label for="opt_Tinh"> Chọn Tỉnh/Thành phố </label><br>
                                    <select class="opt_select opt_Tinh" name="opt_Tinh" id="opt_Tinh"
                                        style="width: 160px; " required>
                                        <option value="">--Tỉnh/Thành phố--</option>
                                        @foreach ($tinh_tp as $tinh)
                                            <option value="{{ $tinh->id }}"
                                                {{ $tinh->ten_tp == $dc[3] ? 'selected' : '' }}>{{ $tinh->ten_tp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-4" style="padding-left: 0;">
                                    <label for="opt_Huyen"> Chọn Quận/Huyện </label><br>
                                    <select class="opt_select opt_Huyen" name="opt_Huyen" id="opt_Huyen"
                                        style="width: 160px; margin-left: 0; " required>
                                        @foreach ($huyen as $h)
                                            <option value="{{ $h->id }}"{{ $h->ten_qh == $dc[2] ? 'selected' : '' }}>
                                                {{ $h->ten_qh }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-4" style="padding-left: 0; padding-right: 0;">
                                    <label for="opt_Xa"> Chọn Xã phường/Thị trấn </label><br>
                                    <select class="opt_Xa" name="opt_Xa" id="opt_Xa" style="width: 160px; " required>
                                        @foreach ($xa as $x)
                                            <option value="{{ $x->id }}"{{ $x->ten_xa == $dc[1] ? 'selected' : '' }}>
                                                {{ $x->ten_xa }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="form-group col-md-12 col-sm-12 " style="padding-left: 10px;">
                                <div class="col-md-4 col-sm-4" style="padding-left: 0;">
                                    <label for="opt_Tinh"> Chọn Tỉnh/Thành phố </label><br>
                                    <select class="opt_select opt_Tinh" name="opt_Tinh" id="opt_Tinh"
                                        style="width: 160px; " required>
                                        <option value="">--Tỉnh/Thành phố--</option>
                                        @foreach ($tinh_tp as $tinh)
                                            <option value="{{ $tinh->id }}">{{ $tinh->ten_tp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-4" style="padding-left: 0;">
                                    <label for="opt_Huyen"> Chọn Quận/Huyện </label><br>
                                    <select class="opt_select opt_Huyen" name="opt_Huyen" id="opt_Huyen"
                                        style="width: 160px; margin-left: 0; " required>
                                        <option value="">--Quận/Huyện--</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-4" style="padding-left: 0; padding-right: 0;">
                                    <label for="opt_Xa"> Chọn Xã phường/Thị trấn </label><br>
                                    <select class="opt_Xa" name="opt_Xa" id="opt_Xa" style="width: 160px; " required>
                                        <option value="">--Xã phường/Thị trấn--</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="form-group" style="padding-left: 10px;">
                            <label for="dia_chi">Số nhà, khóm/ấp</label>
                            <input type="text" class="form-control" name="dia_chi" id="dia_chi"
                                value="{{ $dc[0] }}" placeholder="Nhập số nhà, khóm/ấp" required>
                            </input>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Chọn hình thức thanh toán</label>
                        <div style="margin-top: 1em">
                            <input type="radio" id="tienmat" name="thanh_toan" class="thanh_toan" value="0"
                                checked />
                            <label for="tienmat"
                                style="
                                font-weight: normal;
                                vertical-align: middle;
                                margin-right: 0.5em;
                            ">
                                Thanh toán khi nhận hàng
                            </label>
                            <input type="radio" id="Paypal" name="thanh_toan" class="thanh_toan" value="1"
                                required />
                            <label for="Paypal"
                                style="
                                font-weight: normal;
                                vertical-align: middle;
                                margin-right: 0.5em;
                            ">
                                Thanh toán Paypal
                            </label>

                            <input type="radio" id="VNPAY" name="thanh_toan" class="thanh_toan" value="2" />
                            <label for="VNPAY" style="font-weight: normal; vertical-align: middle">
                                Thanh toán bằng VNPAY
                            </label>
                            <input type="hidden" name="tien_usd" value="{{ round(Cart::total(0, '', '') / 23512, 2) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ghi_chu">Ghi chú</label>
                        <textarea class="form-control" name="ghi_chu" id="ghi_chu"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Thanh toán" class="btn btn-primary" />
                    </div>
                </form>
            </div>
            <div class="col-sm-6">
                <h4><b> Các sản phẩm đã chọn</b></h4>
                <table class="table table-bordered" style="vertical-align: middle">
                    <tr class="info">
                        <th>Sản phẩm</th>
                        <th>Giá bán</th>
                        <th>Số lượng</th>
                        <th>Tổng tiền</th>
                    </tr>
                    @foreach (Cart::content() as $nd)
                        <tr>
                            <td>
                                {{ $nd->name }}
                            </td>

                            <td>
                                {{ number_format($nd->price, 0, ',', '.') }} đ
                            </td>

                            <td>
                                {{ $nd->qty }}
                            </td>
                            <td>
                                {{ number_format($nd->price * $nd->qty, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    @endforeach

                </table>
                <table style="float: right;">
                    <tr>
                        <td><b>Thuế:</b></td>
                        <td style="width: 8em; font-size: 1.5rem; text-align: right;">
                            <strong>{{ Cart::tax(0, ',', '.') }} đ</strong>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Thành tiền:</b></td>
                        <td style="width: 8em; font-size: 1.5rem; text-align: right;">
                            <strong>{{ Cart::total(0, ',', '.') }} đ</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    @endsection @section('js')
    <script src="{{ asset('frontend/assets_theme/plugins/uniform/jquery.uniform.min.js') }}" type="text/javascript">
    </script>

    <script src="{{ asset('frontend/assets_theme/plugins/rateit/src/jquery.rateit.js') }}" type="text/javascript"></script>
    <script src="{{ asset('frontend/assets_theme/plugins/carousel/carousel.js') }}" type="text/javascript"></script>
    <script src="{{ asset('frontend/js/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.opt_select').on('change', function() {
                var action = $(this).attr('id');
                var id_diachi = $(this).val();
                var _token = $('input[name="_token"]').val();
                var kq = '';
                if (action == "opt_Tinh")
                    kq = 'opt_Huyen';
                else
                    kq = 'opt_Xa';
                $.ajax({
                    url: '{{ route('diachi') }}',
                    method: 'POST',
                    data: {
                        action: action,
                        id_diachi: id_diachi,
                        _token: _token
                    },
                    success: function(data) {
                        $('#' + kq).html(data);
                    }
                })
            });
        })

        function kiemTraSDT(event) {

            var dt = document.getElementById("sdt").value;
            var dt2 = document.getElementById("sdt");
            var kt = /^(0?)(3[2-9]|5[6|8|9]|7[0|6-9]|8[0-6|8|9]|9[0-4|6-9])[0-9]{7}$/.test(dt);

            if (isNaN(dt)) {
                event.preventDefault()
                dt2.setCustomValidity('Giá trị phải là số');
                dt2.reportValidity();
            } else if (dt.length != '10') {
                event.preventDefault()
                dt2.setCustomValidity('Số điện thoại phải đủ 10 số');
                dt2.reportValidity();
            } else if (kt == false) {
                event.preventDefault()
                dt2.setCustomValidity('Định dạng số điện thoại không đúng');
                dt2.reportValidity();
            } else {
                dt2.setCustomValidity('');
                dt2.reportValidity();
            }
        }
    </script>
@endsection
