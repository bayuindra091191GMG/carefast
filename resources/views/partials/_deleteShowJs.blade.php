<script>
    $('.modal-footer').on('click', '.delete', function() {
        $.ajax({
            type: 'POST',
            url: '{{ route($routeUrl) }}',
            data: {
                '_token': '{{ csrf_token() }}',
                'id': $('#deleted-id').val()
            },
            success: function(data) {
                if ((data.errors)){
                    setTimeout(function () {
                        toastr.error('Gagal menghapus data!!', 'Peringatan', {timeOut: 6000, positionClass: "toast-top-center"});
                    }, 500);
                }
                else{
                    window.location = '{{ route($redirectUrl) }}';
                }
            },
            error: function (data) {
                alert("Error");
            }
        });
    });
</script>