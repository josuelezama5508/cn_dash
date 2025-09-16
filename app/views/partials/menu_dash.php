
    <header>
        <div style="position: fixed; bottom: 0; /* float: right; */ z-index: 600; right: 0; margin-bottom: 30px;margin-right: 30px;">
            <button class="btn btn-primary" type="button" style="display: none !important; background-color: #337AB7; padding: 0 !important; display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; " id="activandomodal">
                <i class="bi bi-plus" style="font-size: 33px; background-color: #337AB7; border-color: #2E6DA4;"></i>
            </button>
        </div>

        <nav style="width: 100%;" id="menu-container">
            <div id="horizontal-menu">
                <div class="content" style="height: 100%;">
                    <ul class="left">
                        <li><a class="row-content-center" href="<?= route('inicio') ?>">Inicio</a></li>
                        <li><a class="row-content-center" href="<?= route('canales') ?>">Canales</a></li>
                        <li><a class="row-content-center" href="<?= route('productos') ?>">Productos</a></li>
                        <li><a class="row-content-center" href="<?= route('dispo_test') ?>">Horarios</a></li>
                        <li><a class="row-content-center" href="<?= route('transportation') ?>">Hoteles</a></li>
                    </ul>
                    <ul class="right" style="align-self: center;">
                        <li id="button-collapse">
                            <button class="row-content-center" ><i class="material-icons" style="font-size: 40px; background: #0277BD; color: white;">person_pin</i></button>
                        </li>
                    </ul>
                </div>
            </div>
            <div id="vertical-menu">
                <ul class="top">
                    <li class="header">
                        <img class="background" src="https://www.totalsnorkelcancun.com/dash/sources/img/background1.jpg" alt="Fondo decorativo del menú lateral">
                    </li>
                </ul>
                <ul class="center menu-options"></ul>
                <ul class="bottom menu-options">
                    <li><a class="row-content-left" href="<?= route('logout') ?>"><i class="material-icons">input</i>Cerrar sesión</a></li>
                </ul>
            </div>
        </nav>

        <nav class="breadcrumb">
            <input type="hidden" name="pagename" value="<?= getCurrentView() ?>">
        </nav>
    </header>