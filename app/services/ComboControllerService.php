<?php
require_once __DIR__ . "/../repositories/ComboProductsRepository.php";

class ComboControllerService
{
    private $comboproducts_repo;

    public function __construct()
    {
        
        $this->comboproducts_repo = new ComboProductsRepository();
    }
    public function getByClave($clave){
        return $this->comboproducts_repo->getByClave($clave);
    }
    public function insert($data)
    {
        return $this->comboproducts_repo->insert($data);
    }
    public function find($id)
    {
        return $this->comboproducts_repo->find($id);
    }
    public function delete(array $data)
    {
        return $this->comboproducts_repo->delete($data);
    }
    public function getTableName(){
        return  $this->comboproducts_repo->getTableName();
    }
    public function update($id, $data){
        return  $this->comboproducts_repo->update($id, $data);
    }
    
    public function getByClaveCombos($clave){
        return $this->comboproducts_repo->getByClaveCombos($clave);
    }

    // --------------------------------------------------------
    // 1. Obtener combo con productos normales
    // --------------------------------------------------------
    public function getProductsComboService($clave, $products_service)
    {
        $is_combo = false;
        $dataCombo = [];

        $dataComboC = $this->getByClave($clave);

        if (!empty($dataComboC) && isset($dataComboC[0]->combos)) {
            $is_combo = true;
            $combosArray = json_decode($dataComboC[0]->combos, true);

            foreach ($combosArray as $comboItem) {
                if (isset($comboItem['productcode'])) {
                    $claveProducto = $comboItem['productcode'];
                    $dataComboP = $products_service->getProductByCodeGroup($claveProducto);
                    $dataCombo[$claveProducto] = is_array($dataComboP) ? $dataComboP : [$dataComboP];
                }
            }
        }

        return ['data' => $dataCombo, 'is_combo' => $is_combo];
    }

    // --------------------------------------------------------
    // 2. Obtener combo por ID
    // --------------------------------------------------------
    public function getComboByIdService($id)
    {
        $data = $this->find((int)$id);
        return ['data' => $data, 'is_combo' => false];
    }

    // --------------------------------------------------------
    // 3. Obtener combo por código
    // --------------------------------------------------------
    public function getComboByCodeService($code)
    {
        $data = $this->getByClave($code);
        return ['data' => $data, 'is_combo' => false];
    }

    // --------------------------------------------------------
    // 4. Obtener combo para plataforma (traducción e idioma)
    // --------------------------------------------------------
    public function getProductsComboPlatformService($search, $products_service)
    {
        $is_combo = false;
        $dataCombo = [];

        $mapLang = ['en' => 1, 'es' => 2, 'pt' => 3];
        $lang = $mapLang[$search['lang']] ?? 1;

        $dataComboC = $this->getByClave($search['productcode']);

        if (!empty($dataComboC) && isset($dataComboC[0]->combos)) {
            $is_combo = true;
            $combosArray = json_decode($dataComboC[0]->combos, true);

            foreach ($combosArray as $comboItem) {
                if (isset($comboItem['productcode'])) {
                    $clave = $comboItem['productcode'];
                    $dataComboP = $products_service->getByClavePlatform(
                        $clave,
                        $search['platform'],
                        $lang
                    );
                    $dataCombo[$clave] = is_array($dataComboP) ? $dataComboP : [$dataComboP];
                }
            }
        }

        return ['data' => $dataCombo, 'is_combo' => $is_combo];
    }

    public function updateComboService($data, $userData, $history_service)
    {
        $comboOld = $this->find($data["id"]);
    
        if (!$comboOld) {
            throw new Exception("Combo no encontrado" . json_encode($comboOld));
        }
    
        $dataUpdateCombo = [
            'combos' => $data['combos'] ?? $comboOld->combos,
            // otros campos a actualizar...
        ];
    
        $this->update($data['id'], $dataUpdateCombo);
    
        $comboNew = $this->find($data['id']);
        $history_service->registrarHistorial(
            'Products',
            $data['id'],
            'update',
            'Actualización de combos',
            $userData->id,
            [
                $this->getTableName() => $comboOld
            ],
            [
                $this->getTableName() => $comboNew,
            ]
        );
    
        return ['message' => 'Reserva reagendada correctamente'];
    }
    

}


