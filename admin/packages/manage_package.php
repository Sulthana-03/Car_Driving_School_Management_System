<?php
require_once('../../config.php');
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM `package_list` where id = '{$_GET['id']}'");
    if ($qry->num_rows > 0) {
        $res = $qry->fetch_array();
        foreach ($res as $k => $v) {
            if (!is_numeric($k))
                $$k = $v;
        }
    }
}
?>

<div class="container-fluid">
    <form action="" id="package-form">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="form-group">
            <label for="name" class="control-label">Package Name</label>
            <input type="text" name="name" id="name" class="form-control form-control-border" placeholder="Package Name" value="<?php echo isset($name) ? $name : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="description" class="control-label">Description</label>
            <textarea rows="3" name="description" id="description" class="form-control form-control-border" placeholder="Write the Package description here." required><?php echo isset($description) ? $description : '' ?></textarea>
        </div>
        <div class="form-group">
            <label for="training_duration" class="control-label">Training Duration</label>
            <input type="text" name="training_duration" id="training_duration" class="form-control form-control-border" placeholder="Training Duration" value="<?php echo isset($training_duration) ? $training_duration : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="cost" class="control-label">Package Cost</label>
            <input type="number" name="cost" id="cost" class="form-control form-control-border text-right" placeholder="Package Cost" value="<?php echo isset($cost) ? $cost : 0 ?>" required>
        </div>
        <!-- Partial Amounts -->
        <div class="form-group">
            <label for="partial1" class="control-label">Partial Amount</label>
            <input type="number" name="partial1" id="partial1" class="form-control form-control-border text-right" value="<?php echo isset($partial1) ? $partial1 : 0 ?>" readonly>
        </div>
        
        <div class="form-group">
            <label for="status" class="control-label">Status</label>
            <select name="status" id="status" class="form-control form-control-border" required>
                <option value="1" <?= isset($status) && $status == 1 ? "selected" : "" ?>>Active</option>
                <option value="0" <?= isset($status) && $status == 0 ? "selected" : "" ?>>Inactive</option>
            </select>
        </div>
    </form>
</div>

<script>
    $(function () {
        function updatePartialAmounts() {
            let cost = parseFloat($('#cost').val());
            if (!isNaN(cost)) {
                let partial = (cost / 2).toFixed(2);
                $('#partial1').val(partial);
                $('#partial2').val(partial);
            } else {
                $('#partial1').val('');
                $('#partial2').val('');
            }
        }

        $('#cost').on('input', updatePartialAmounts);
        updatePartialAmounts(); // Initial call in case cost is prefilled

        $('#uni_modal #package-form').submit(function (e) {
            e.preventDefault();
            var _this = $(this)
            $('.pop-msg').remove()
            var el = $('<div>')
            el.addClass("pop-msg alert")
            el.hide()
            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_package",
                data: new FormData(_this[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err)
                    alert_toast("An error occurred", 'error');
                    end_loader();
                },
                success: function (resp) {
                    if (resp.status == 'success') {
                        location.reload();
                    } else if (!!resp.msg) {
                        let errorText = resp.msg;
                        if (resp.err) {
                            errorText += " | Debug: " + resp.err;
                        }
                        el.addClass("alert-danger");
                        el.text(errorText);
                        _this.prepend(el);
                    } else {
                        el.addClass("alert-danger");
                        el.text("An error occurred due to an unknown reason.");
                        _this.prepend(el);
                    }
                    el.show('slow');
                    end_loader();
                }
            })
        })
    })
</script>
