<section style="padding: 4px;">
    <div id="contenrMsj"></div>
    <!--  -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!--  -->
        <form id="form-add-tags" style="display: flex; flex-direction: column; gap: 10px;">
            <!--  -->
            <div id="form-tag-data">
                <div class="form-group">
                    <label style="font-weight: 700;">Referencia:</label>
                    <input type="text" name="tagreference" class="form-control ds-input">
                </div>
            </div>
            <!--  -->
            <div id="form-tag-items" style="display: flex; flex-direction: column; gap: 10px;"></div>
            <!--  -->
        </form>
        <!--  -->
        <div style="display: flex; flex-direction: row; gap: 10px;">
            <button id="addProductItem" class="btn-icon"><i class="material-icons left">add</i>ADD TAG</button>
            <button id="saveProductItem" class="btn-icon"><i class="material-icons left">send</i>SAVE</button>
        </div>
        <!--  -->
    </div>
</section>


<script>
    var itemTagCount = 0;


    $(document).ready(function() {
        new_item(1);
        new_item(2);
        $(document).on("click", "#addProductItem", function() {
            add_item_tag();
        });

        $(document).on("click", "#saveProductItem", function() {
            save_item_tag();
        });
    });


    function new_item(lang_id = 0) {
        let divname = `divLang-${itemTagCount}`;
        let btnDelete = lang_id != 1 ? '<div style="display: flex; flex-direction: column;"><label style="font-weight: 700; color: transparent;">.</label><div class="row-content-center" style="height: 100%;"><i class="small material-icons delete-item" style="color: red; cursor: pointer;">cancel</i></div></div>' : '';

        $("#form-tag-items").append(`
            <div style="width: 100%; display: flex; flex-direction: row; gap: 6px" class="item-tag-${itemTagCount}">
                <div class="form-group" style="flex: 1;">
                    <label style="font-weight: 700;">Idioma:</label>
                    <div id="${divname}"></div>
                </div>
                <div class="form-group" style="flex: 3;">
                    <label style="font-weight: 700;">Tagname:</label>
                    <input type="text" name="tagname[]" class="form-control ds-input input-tagname">
                </div>
                ${btnDelete}
            </div>`);

        $.ajax({
            url: `${window.url_web}/widgets/`,
            type: 'POST',
            data: {
                'widget': 'select',
                'category': 'language',
                'name': 'language[]',
                'selected_id': lang_id,
            },
            success: async function(response) {
                $(`#${divname}`).html(response);
            }
        });

        $("#form-tag-items").on("click", ".delete-item", function() {
            remove_item_tag(this);
        });

        itemTagCount++;
    }


    function add_item_tag() {
        let isValid = tag_items_are_valid();
        if (!isValid) return;
        new_item();
    }


    function tag_items_are_valid() {
        function test(input) {
            let ban, msg;
            let campo = $(input).attr("name");
            let texto = $(input).val();

            switch (campo) {
                case 'language[]':
                    [ban, msg] = validate_data(texto, regexID);
                    break;
                case 'tagname[]':
                    [ban, msg] = validate_data(texto, regexName);
                    break;
            }

            return result_validate_data(input, campo, ban, msg);
        }

        let booleanArray = [];
        $("#form-tag-items :input").each(function() {
            let boolean = test(this);
            booleanArray.push(boolean);
        });

        return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
    }


    function tag_data_are_valid() {
        function test(input) {
            let ban, msg;
            let campo = $(input).attr("name");
            let texto = $(input).val();

            switch (campo) {
                case 'tagreference':
                    [ban, msg] = validate_data(texto, regexName);
                    break;
            }

            return result_validate_data(input, campo, ban, msg);
        }

        let booleanArray = [];
        $("#form-tag-data :input").each(function() {
            let boolean = test(this);
            booleanArray.push(boolean);
        });

        return booleanArray.length > 0 && booleanArray.every((valor) => valor === true);
    }


    function remove_item_tag(item) {
        let className = $(item).closest("[class^='item-tag-']");
        if (className) {
            className.fadeOut(500);
        }

        setTimeout(function() {
            className.remove();
        }, 500);
    }


    function save_item_tag() {
        let valid_1 = tag_data_are_valid();
        let valid_2 = tag_items_are_valid();

        if (valid_1 && valid_2) {
            let uploadScreen = upload_screen("Espere.", "Capturando datos del tag.");
            let formData = new FormData(document.getElementById("form-add-tags"));

            fetchAPI('tags', 'POST', formData)
                .then(async (response) => {
                    const status = response.status;
                    const text = await response.json();

                    if (status == 201) {
                        setTimeout(() => {
                            updateView = true;
                            uploadScreen.close();
                            if (updateView) location.reload();
                        }, 900);
                    } else {
                        uploadScreen.close();
                        $("#contenrMsj").html(`<div style="margin-bottom: 10px;"><p style="background: #ff3100; padding: 5px 15px; color: white; margin-bottom: 0;"><b>Error:</b> ${text.message}</p></div>`);
                    }

                    setTimeout(() => {
                        $("#contenrMsj").html('');
                    }, 2000);
                });
        }
    }
</script>