function create_select(name, category, div, selected = 0) {
    $.ajax({
        url: `${window.url_web}/widgets/`,
        type: "POST",
        data: {
            widget: "select",
            category: category,
            name: name,
            selected_id: selected,
            'id_user': window.userInfo.user_id
        },
        success: function (response) {
            $(div).html(response);
        },
    });
}
