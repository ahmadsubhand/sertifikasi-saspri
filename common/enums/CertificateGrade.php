<?php 

namespace common\enums;

class CertificateGrade
{
    const A = 'a';
    const AB = 'ab';
    const B = 'b';
    const BC = 'bc';
    const C = 'c';

    public static function list()
    {
        return [
            self::A => 'TERSERTIFIKASI DENGAN PUJIAN TERTINGGI COMMENDABLE (90-100%)',
            self::AB => 'TERSERTIFIKASI DI ATAS STANDARD (EXCEED EXPECTATION) (75-89%)',
            self::B => 'TERSERTIFIKASI SESUAI STANDARD (MEET EXPECTATION) (60-74%)',
            self::BC => 'PROSES SERTIFIKASI DIULANG DALAM SATU TAHUN SETELAH DILAKUKAN USAHA PERBAIKAN (50-59%)',
            self::C => 'PROSES SERTIFIKASI DIULANG KEMBALI DALAM 2 TAHUN (<50%)',
        ];
    }

    public static function values()
    {
        return array_keys(self::list());
    }
}