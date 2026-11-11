<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SigespEmployee extends Model
{
    protected $connection = 'pgsql_sigesp';

    protected $table = 'vista_emp_activos_final';

    protected $primaryKey = 'cedula';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'telefono',
        'correo',
        'fecha_nacimiento',
        'fecha_ingreso',
        'nacionalidad',
        'cargo',
        'tipo_nomina',
        'unidad_administrativa'
    ];
}
