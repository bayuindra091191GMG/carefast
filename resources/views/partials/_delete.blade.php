<div id="deleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <h3 class="text-center">Apakah anda yakin ingin menghapus data ini?</h3>
                <br />

                <form role="form">
                    <input type="hidden" id="deleted-id" name="deleted-id"/>
                </form>

                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal">
                        <span class='glyphicon glyphicon-remove'></span> No
                    </button>
                    <button type="submit" class="btn btn-danger delete">
                        <span class='glyphicon glyphicon-trash'></span> Yes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>