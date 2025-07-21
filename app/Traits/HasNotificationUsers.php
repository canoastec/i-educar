<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasNotificationUsers
{
    /**
     * Busca usuários que devem receber notificações baseado no processo e escola
     *
     * @param int $process
     * @param int $school
     * @return array
     */
    public function getUsers($process, $school)
    {
        return DB::table('pmieducar.usuario as u')
            ->select('u.cod_usuario')
            ->join('pmieducar.menu_tipo_usuario as mtu', 'mtu.ref_cod_tipo_usuario', 'u.ref_cod_tipo_usuario')
            ->join('pmieducar.tipo_usuario as tu', 'tu.cod_tipo_usuario', 'u.ref_cod_tipo_usuario')
            ->join('public.menus as m', 'm.id', 'mtu.menu_id')
            ->leftJoin('pmieducar.escola_usuario as eu', 'eu.ref_cod_usuario', 'u.cod_usuario')
            ->where('m.process', $process)
            ->where('u.ativo', 1)
            ->where(function ($q) use ($school) {
                $q->where('eu.ref_cod_escola', $school);
                $q->orWhere('tu.nivel', '<=', \App_Model_NivelTipoUsuario::INSTITUCIONAL);
            })
            ->get()
            ->toArray();
    }
}
