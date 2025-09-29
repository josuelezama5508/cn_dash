// 🔹 Solo hace la consulta y devuelve la respuesta JSON
async function fetch_hoteles() {
    try {
        const response = await fetchAPI("hotel?getAllDispo", "GET");
        const data = await response.json();

        if (response.status === 200 && data.data?.length) {
            return data.data; // ✅ devuelve la empresa encontrada
        } else {
            console.warn(data.message || "No se pudo cargar la empresa.");
            return null;
        }
    } catch (error) {
        console.error("Error al obtener la empresa:", error);
        return null;
    }
}
function render_hotels(hotels) {
    const $input = $("#hotelInput");
    const $dropdown = $("#hotelDropdown");
    let currentIndex = -1;

    function showDropdown(matches) {
        $dropdown.empty();
        currentIndex = -1; // reset highlight

        if (matches.length > 0) {
            matches.forEach(h => {
                $dropdown.append(`<li style="padding:5px; cursor:pointer;">${h.nombre}</li>`);
            });
            $dropdown.show();
        } else {
            // ✅ No hay hoteles
            $dropdown.append('<li style="padding:5px; color:#999; cursor:default;">NO EXISTEN HOTELES</li>');
            $dropdown.show();

            // 🔹 Limpiar input
            $input.val("");

            // 🔹 Hint PENDIENTE (puede ser placeholder o un elemento extra)
            $input.attr("placeholder", "PENDIENTE");
        }
    }

    $input.on("input", function () {
        const query = $(this).val().toLowerCase();
        const matches = hotels.filter(h => h.nombre.toLowerCase().includes(query));
        showDropdown(matches);
    });

    $input.on("focus", function () {
        if ($(this).val() === "") {
            showDropdown(hotels);
        }
    });

    // Resaltar al pasar el mouse
    $dropdown.on("mouseenter", "li", function () {
        $dropdown.find("li").removeClass("active");
        $(this).addClass("active");
        currentIndex = $(this).index();
    });

    $dropdown.on("click", "li", function () {
        const text = $(this).text();
        if (text !== "NO EXISTEN HOTELES") {
            $input.val(text);
            $dropdown.hide();
            $input.attr("placeholder", ""); // limpiar hint
        }
    });

    // Navegación con teclado
    $input.on("keydown", function(e) {
        const $items = $dropdown.find("li").not(':contains("NO EXISTEN HOTELES")');
        if ($items.length === 0) return;

        if (e.key === "ArrowDown") {
            e.preventDefault();
            currentIndex = (currentIndex + 1) % $items.length;
            $items.removeClass("active");
            $items.eq(currentIndex).addClass("active");
            scrollToView($items.eq(currentIndex));
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            currentIndex = (currentIndex - 1 + $items.length) % $items.length;
            $items.removeClass("active");
            $items.eq(currentIndex).addClass("active");
            scrollToView($items.eq(currentIndex));
        } else if (e.key === "Enter") {
            e.preventDefault();
            if (currentIndex >= 0) {
                $input.val($items.eq(currentIndex).text());
                $dropdown.hide();
                $input.attr("placeholder", "");
            }
        }
    });

    function scrollToView($item) {
        const container = $dropdown[0];
        const item = $item[0];
        if (item.offsetTop < container.scrollTop) {
            container.scrollTop = item.offsetTop;
        } else if (item.offsetTop + item.offsetHeight > container.scrollTop + container.offsetHeight) {
            container.scrollTop = item.offsetTop + item.offsetHeight - container.offsetHeight;
        }
    }

    $(document).click(function(e){
        if (!$(e.target).closest('#hotelInput, #hotelDropdown').length) {
            $dropdown.hide();
        }
    });
}




function setupHotelSearch(hotels) {
    const $input = $("#hotelSearchInput");
    const $select = $("#hotelSelect");

    $input.off("input").on("input", function () {
        const query = $(this).val().toLowerCase();

        // Encontrar primer match
        const match = hotels.find(h => h.nombre.toLowerCase().includes(query));
        if (match) {
            $select.val(match.nombre);
        } else {
            $select.val("");
        }
    });

    // // Enfocar input oculto al enfocar el select
    // $select.off("focus").on("focus", function () {
    //     $input.val("").focus();
    // });

    // // También enfocar al hacer click
    // $select.off("click").on("click", function () {
    //     $input.val("").focus();
    // });
}

