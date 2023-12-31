$(function () {
    $(document).on("click", ".action_del", Delete);
    $(document).on("click", ".action_res", Khoiphuc);
    $(document).on("click", ".action_huy", Huy);
    $(".tag_select").select2({
        placeholder: " - Nhập tag sản phẩm -",
        tokenSeparators: [","],
    });
});

//sử dụng sweetalert2 để hiện bảng thông báo, ajax
function Delete(even) {
    even.preventDefault();
    let urlRequest = $(this).data("url"); //lấy đường dẫn url
    let that = $(this);
    Swal.fire({
        title: "Bạn có chắc chắn xóa?",
        text: "Dữ liệu khác có thể bị xoá theo",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Hủy",
        confirmButtonText: "Xác nhận xóa",
    }).then((result) => {
        if (result.value) {
            $.ajax({
                type: "GET",
                url: urlRequest,
                success: function (data) {
                    if (data.code == 200) {
                        Swal.fire(
                            "Đã xóa!",
                            "Dữ liệu đã được xóa.",
                            "success"
                        ).then(function () {
                            location.reload();
                        });
                    }
                },
                error: function () {},
            });
        }
    });
}
function Khoiphuc(even) {
    even.preventDefault();
    let urlRequest = $(this).data("url"); //lấy đường dẫn url
    let that = $(this);
    Swal.fire({
        title: "Bạn có chắc chắn muốn khôi phục?",
        text: "",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Hủy",
        confirmButtonText: "Khôi phục",
    }).then((result) => {
        if (result.value) {
            $.ajax({
                type: "GET",
                url: urlRequest,
                success: function (data) {
                    if (data.code == 200) {
                        Swal.fire(
                            "Đã khôi phục!",
                            "Dữ liệu đã được khôi phục.",
                            "success"
                        ).then(function () {
                            location.reload();
                        });
                    }
                    if (data.success === true)
                        Swal.fire("Khôi phục thất bại!", data.message, "error");
                },
                error: function () {},
            });
        }
    });
}

function Huy(even) {
    even.preventDefault();
    let urlRequest = $(this).data("url"); //lấy đường dẫn url
    Swal.fire({
        title: "Bạn có chắc chắn hủy?",
        text: "Nó không thể khôi phục lại.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Huỷ",
        confirmButtonText: "Xác nhận huỷ",
    }).then((result) => {
        if (result.value) {
            $.ajax({
                type: "GET",
                url: urlRequest,
                success: function (data) {
                    if (data.code == 200) {
                        Swal.fire(
                            "Đã huỷ!",
                            "Hủy đơn hàng thành công.",
                            "success"
                        ).then(function () {
                            location.reload();
                        });
                    }
                },
                error: function () {},
            });
        }
    });
}
