
                <ul id="product-menu">
                        <?php if($level === 'master' || $level === 'administrador'){?>
                        <li>
                            <a class="row-content-left" href="<?= route('productos') ?>">
                                <i class="small material-icons">more</i>
                                Productos
                            </a>
                        </li>
                        <li>
                            <a class="row-content-left" href="<?= route('tags') ?>">
                                <i class="small material-icons">more</i>
                                Tags
                            </a>
                        </li>
                        <li>
                            <a class="row-content-left" href="<?= route('codigopromo') ?>">
                                <i class="small material-icons">more</i>
                                Codigos promo
                            </a>
                        </li>
                        <?php } ?>
                    
                    <li>
                        <a class="row-content-left" href="<?= route('prospectos') ?>">
                            <i class="small material-icons">more</i>
                            Prospectos
                        </a>
                    </li>
                </ul>
