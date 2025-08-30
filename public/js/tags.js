$(document).ready(function() {
    create_form_tags();

    registered_tags('');
    $("[name='search']").on("input", function() { registered_tags($(this).val()); });
});


const registered_tags = async (condition) => {
    fetchAPI(`tags?search=${condition}`, 'GET')
      .then(async (response) => {
        const status = response.status;
        const text = await response.json();

        if (status == 200) {
            let rows = "";
            let data = text.data;

            data.forEach((element) => {
                let tagname = "";
                Object.entries(element.tagname).forEach(([key, value]) => {
                    tagname += `<strong style="font-size: 14px; margin: 0;">${key.toUpperCase()}:</strong><p style="font-size: 14px; margin: 0;">${value}</p>`;
                });

                rows += `
                        <tr>
                            <td class="item-box">
                                <div class="item-box-container">
                                    <div class="item-box-data">
                                        <div class="item-code"><a href="${window.url_web}/tags/details/${element.id}">${element.reference}</a></div>
                                        <div class="item-name" style="grid-column: span 2;">
                                            <div style="flex: 2; display: grid; grid-template-columns: auto 1fr;gap: 10px; align-items: center;justify-content: left;">${tagname}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
            });

            $("#RBuscador").html(rows);
        }
      })
      .catch((error) => {});
};


function create_form_tags() {
    return $.ajax({
        url: `${window.url_web}/form/add_tags`,
        success: function(response) {
            $("#divTags").html(response);
        }
    });
}