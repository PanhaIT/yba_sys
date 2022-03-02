<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Helper
 *
 * @author UDAYA
 */
App::import('model', 'District');
App::import('model', 'Commune');
App::import('model', 'Village');

class AddressComponent extends Object {

    var $components = array('Helper');

    function districtList() {
        $districtModel = new District();
        $districtLists = $districtModel->find("all", array("conditions" => array("District.is_active != 2"), 'order' => array('District.name')));

        $options = array();
        foreach ($districtLists as $districtList) {
            $arrDistrict = array('value' => $districtList['District']['id'], 'name' => $districtList['District']['name'], 'class' => $districtList['District']['province_id']);
            array_push($options, $arrDistrict);
        }
        return $options;
    }

    function communeList() {
        $communeModel = new Commune();
        $communeLists = $communeModel->find("all", array("conditions" => array("Commune.is_active != 2"), 'order' => array('Commune.name')));

        $options = array();
        foreach ($communeLists as $communeList) {
            $arrCommune = array('value' => $communeList['Commune']['id'], 'name' => $communeList['Commune']['name'], 'class' => $communeList['Commune']['district_id']);
            array_push($options, $arrCommune);
        }
        return $options;
    }

    function villageList() {
        $villageModel = new Village();
        $villageLists = $villageModel->find("all", array("conditions" => array("Village.is_active != 2"), 'order' => array('Village.name')));

        $options = array();
        foreach ($villageLists as $villageList) {
            $arrVillage = array('value' => $villageList['Village']['id'], 'name' => $villageList['Village']['name'], 'class' => $villageList['Village']['commune_id']);
            array_push($options, $arrVillage);
        }
        return $options;
    }

    function villageById($village_id) {
        $villageModel = new Village();
        $communeId = $villageModel->find("first", array("conditions" => array("Village.id" => $village_id)));
        $villageLists = $villageModel->find("all", array("conditions" => array("Village.is_active != 2", "Village.commune_id" => $communeId['Village']['commune_id']), 'order' => array('Village.name')));
        $options = array();
        foreach ($villageLists as $villageList) {
            $arrVillage = array('value' => $villageList['Village']['id'], 'name' => $villageList['Village']['name']);
            array_push($options, $arrVillage);
        }
        return $options;
    }

    function getAddressByCustomerId($customer_id){
        $districtModel = new District();
        $address = array();
        $sql = "SELECT Province.id, Province.name, District.id, District.name, Commune.id, Commune.name, Village.id, Village.name FROM provinces AS Province
                    INNER JOIN districts AS District ON Province.id = District.province_id
                    INNER JOIN communes AS Commune ON District.id = Commune.district_id
                    INNER JOIN villages AS Village ON Commune.id = Village.commune_id
                    INNER JOIN customers AS Customer ON Village.id = Customer.village_id
                    WHERE Customer.id = '$customer_id' ";

        $address = $districtModel->query($sql);
        return $address;
    }
}

?>