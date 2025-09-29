<section style="padding: 4px;">
    <div id="form-tagname-product" style="height: 400px; display: flex; flex-direction: column; gap: 10px;">
        <!--  -->
        <input type="hidden" name="productcode" value="<?= $params['productcode'] ?>">
        <input type="search" name="search" class="form-control ds-input" placeholder="Search...">
        <!--  -->
        <div style="width: 100%; height: 100%; overflow: auto;" id="tag-container">
            <table class="table table-scrollbar table-products" style="margin: 0; width: 100%; border-collapse: collapse; display: table;">
                <tbody id="RTagBuscador" style="display: table; width: 100%; overflow: auto;"></tbody>
            </table>
        </div>
        <!--  -->
    </div>
</section>


<script>
    var updateView = false;
    var uploadScreen;


    $(document).ready(function() {
        registered_tags('');
        $("#form-tagname-product [name='search']").on("input", function() {
            registered_tags($(this).val());
        });
    });


    var registered_tags = async (condition) => {
        let productcode = $("[name='productcode']").val();

        fetchAPI(`tags?search=${condition}&productcode=${productcode}`, 'GET')
            .then(async (response) => {
                const status = response.status;
                const text = await response.json();

                if (status == 200) {
                    let rows = '';
                    let data = text.data;

                    data.forEach((element) => {
                        let tagnameBox = '';
                        Object.entries(element.tagname).forEach(([key, value]) => {
                            tagnameBox += `<strong style="font-size: 14px; margin: 0;">${key.toUpperCase()}:</strong><p style="font-size: 14px; margin: 0;">${value}</p>`;
                        });

                        rows += `
                            <tr>
                                <td class="item-box">
                                    <div class="item-box-container">
                                        <div class="row-content-left" style="width: 100%; padding: 20px 30px;">
                                            <div class="row-content-left" style="width: 30%;">
                                                <input type="checkbox" id="item-${element.id}" name="tags[]" style="width: 20px;">
                                                <div class="item-code" style="min-width: 70px;">
                                                    <label for="item-${element.id}" style="font-weight: bold; color: royalblue; display: block; max-width: 200px; word-wrap: break-word;white-space: normal;">${element.reference}</label>
                                                </div>
                                            </div>
                                            <div style="width: 80%;">
                                                <div style="flex: 2; display: grid; grid-template-columns: auto 1fr;gap: 10px; align-items: center;justify-content: left;">${tagnameBox}</div>
                                            <div>
                                        </div>
                                    </div>
                                </td>
                            </tr>`;
                    });

                    $("#RTagBuscador").html(rows);
                }
            });
    };


    function sendEvent(widget = null) {
        let productcode = $("[name='productcode']").val();
        var selectedCheckboxes = $('input[type="checkbox"][id^="item-"]:checked').map(function() {
            return $(this).attr("id");
        }).get();
        if (selectedCheckboxes.length == 0) {
            $("#tag-container").css("box-shadow", "0px 0px 8px rgba(255, 0, 0, 0.6)").fadeIn("slow");
            setTimeout(() => {
                $("#tag-container").css("box-shadow", "none");
            }, 2000);
            return;
        }

        let formData = new FormData();
        formData.append("productcode", productcode);
        selectedCheckboxes.forEach((value, key) => {
            formData.append("tags[]", value);
        });

        let uploadScreen = upload_screen("Espere.", "Capturando tags del producto.");
        fetchAPI('itemproduct', 'POST', formData)
            .then(async (response) => {
                const status = response.status;
                const text = await response.json();

                if (status == 201) {
                    setTimeout(() => {
                        uploadScreen.close();
                        location.reload();
                    }, 900);
                }
            });
    }


    function cancelEvent(widget = null) {
        if (updateView) location.reload();
        if (widget) widget.close();
    }
</script>