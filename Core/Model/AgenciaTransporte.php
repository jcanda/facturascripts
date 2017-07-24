<?php
/**
 * This file is part of facturacion_base
 * Copyright (C) 2015         Pablo Peralta
 * Copyright (C) 2015-2016    Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Model;

/**
 * Agencia de transporte de mercancías.
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class AgenciaTransporte
{

    use Base\ModelTrait;
    use Base\ContactInformation;

    /**
     * Clave primaria. Varchar(8).
     * @var string
     */
    public $codtrans;

    /**
     * Nombre de la agencia.
     * @var string
     */
    public $nombre;

    /**
     * TRUE => activo.
     * @var bool
     */
    public $activo;

    /**
     * AgenciaTransporte constructor.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->init(__CLASS__, 'agenciastrans', 'codtrans');
        if (is_null($data) || empty($data)) {
            $this->clear();
        } else {
            $this->loadFromData($data);
        }
    }

    /**
     * Resetea los valores de todas las propiedades modelo.
     */
    public function clear()
    {
        $this->clearContactInformation();

        $this->codtrans = null;
        $this->nombre = null;
        $this->activo = true;
    }

    /**
     * Devuelve la url donde ver/modificar estos datos
     * @return string
     */
    public function url()
    {
        $result = 'index.php?page=AgenciaTransporte';
        if ($this->codtrans !== null) {
            $result .= '_card&cod=' . $this->codtrans;
        }

        return $result;
    }

    /**
     * Esta función es llamada al crear la tabla del modelo. Devuelve el SQL
     * que se ejecutará tras la creación de la tabla. útil para insertar valores
     * por defecto.
     * @return string
     */
    private function install()
    {
        return 'INSERT INTO ' . $this->tableName() . ' (codtrans, nombre, web, activo) VALUES ' .
            "('ASM', 'ASM', 'http://es.asmred.com/', '" . $this->bool2str(true) . "')," .
            "('TIPSA', 'TIPSA', 'http://www.tip-sa.com/', '" . $this->bool2str(true) . "')," .
            "('SEUR', 'SEUR', 'http://www.seur.com', '" . $this->bool2str(true) . "');";
    }
}
