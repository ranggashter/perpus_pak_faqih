<?php
/**
 * Filter data buku yang digunakan bersama oleh tabel dan fitur export.
 */

if (!function_exists('buildBukuFilter')) {
    /**
     * Menyusun kondisi WHERE dari parameter URL yang sama dengan filter tabel.
     *
     * @return array{where:string, params:array, types:string, search:string, kategori:string}
     */
    function buildBukuFilter()
    {
        $search = (isset($_GET['search']) && is_string($_GET['search']))
            ? trim($_GET['search'])
            : '';
        $kategori = (isset($_GET['kategori']) && is_string($_GET['kategori']))
            ? trim($_GET['kategori'])
            : '';

        $conditions = ['1=1'];
        $params = [];
        $types = '';

        if ($kategori !== '') {
            $conditions[] = 'kategori = ?';
            $params[] = $kategori;
            $types .= 's';
        }

        if ($search !== '') {
            $conditions[] = '(judul LIKE ? OR penulis LIKE ? OR kode_buku LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types .= 'sss';
        }

        return [
            'where' => ' WHERE ' . implode(' AND ', $conditions),
            'params' => $params,
            'types' => $types,
            'search' => $search,
            'kategori' => $kategori,
        ];
    }
}