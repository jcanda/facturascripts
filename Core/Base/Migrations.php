<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2020-2024 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Base;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\FormaPago;
use FacturaScripts\Dinamic\Model\LogMessage;
use FacturaScripts\Dinamic\Model\Serie;

/**
 * Description of Migrations
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
final class Migrations
{
    /** @var DataBase */
    private static $database;

    public static function run(): void
    {
        self::clearLogs();
        self::fixSeries();
        self::fixFormasPago();
    }

    private static function clearLogs(): void
    {
        $logModel = new LogMessage();
        $where = [new DataBaseWhere('channel', 'master')];
        if ($logModel->count($where) < 20000) {
            return;
        }

        // cuando hay miles de registros en el canal master, eliminamos los antiguos para evitar problemas de rendimiento
        $date = date("Y-m-d H:i:s", strtotime("-1 month"));
        $sql = "DELETE FROM logs WHERE channel = 'master' AND time < '" . $date . "';";
        self::db()->exec($sql);
    }

    private static function db(): DataBase
    {
        if (self::$database === null) {
            self::$database = new DataBase();
        }

        return self::$database;
    }

    // versión 2024.5, fecha 15-04-2024
    private static function fixFormasPago(): void
    {
        // forzamos la comprobación de la tabla formas_pago
        new FormaPago();

        // recorremos las tablas de documentos de compra o venta
        $tables = [
            'albaranescli', 'albaranesprov', 'facturascli', 'facturasprov', 'pedidoscli', 'pedidosprov',
            'presupuestoscli', 'presupuestosprov'
        ];
        foreach ($tables as $table) {
            // buscamos aquellos códigos de pago que no estén en la tabla formaspago
            $sql = "SELECT DISTINCT codpago FROM " . $table . " WHERE codpago NOT IN (SELECT codpago FROM formaspago);";
            foreach (self::db()->select($sql) as $row) {
                $formaPago = new FormaPago();
                $formaPago->codpago = $row['codpago'];
                $formaPago->descripcion = Tools::lang()->trans('deleted');
                $formaPago->save();
            }
        }
    }

    // version 2023.06, fecha 07-10-2023
    private static function fixSeries(): void
    {
        // forzamos la comprobación de la tabla series
        new Serie();

        // actualizamos con el tipo R la serie marcada como rectificativa en el panel de control
        $serieRectifying = Tools::settings('default', 'codserierec', '');
        if (empty($serieRectifying)) {
            return;
        }

        $sqlUpdate = "UPDATE series SET tipo = 'R' WHERE codserie = " . self::db()->var2str($serieRectifying) . ";";
        self::db()->exec($sqlUpdate);
    }
}
