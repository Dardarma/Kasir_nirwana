<?php

namespace Tests\Feature;

use App\Http\Middleware\PreserveListQuery;
use Illuminate\Http\Request;
use Tests\TestCase;

class PreserveListQueryTest extends TestCase
{
    public function test_filters_survive_mutations_across_all_list_pages(): void
    {
        foreach (['barang', 'sub-barang', 'customers', 'satuan', 'users', 'roles', 'permissions', 'kasir/list', 'pembelian/list', 'pemakaian/list', 'produksi/list'] as $page) {
            foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
                $request = Request::create('http://localhost/'.$page.'/1', $method);
                $request->headers->set('referer', 'http://localhost/'.$page.'?search=tahu%20bakso&paginate=25&page=2&customer=3');
                $response = (new PreserveListQuery)->handle($request, fn () => redirect('/'.$page)->with('success', 'Tersimpan'));

                $this->assertSame('http://localhost/'.$page.'?search=tahu%20bakso&paginate=25&page=2&customer=3', $response->getTargetUrl());
                $this->assertSame('Tersimpan', session('success'));
            }
        }
    }

    public function test_explicit_redirect_query_takes_precedence_and_fragment_is_preserved(): void
    {
        $request = Request::create('http://localhost/barang/1', 'PUT');
        $request->headers->set('referer', 'http://localhost/barang?search=tahu&page=3');
        $response = (new PreserveListQuery)->handle($request, fn () => redirect('/barang?page=1#data'));

        $this->assertSame('http://localhost/barang?search=tahu&page=1#data', $response->getTargetUrl());
    }

    public function test_unrelated_redirects_external_sources_and_get_navigation_are_unchanged(): void
    {
        foreach ([
            ['POST', 'http://localhost/barang?search=tahu', '/login'],
            ['POST', 'https://external.example/barang?search=tahu', '/barang'],
            ['POST', 'http://localhost/barang?search=tahu', 'https://external.example/barang'],
            ['GET', 'http://localhost/barang?search=tahu', '/barang'],
            ['POST', null, '/barang'],
        ] as [$method, $referer, $destination]) {
            $request = Request::create('http://localhost/barang', $method);
            if ($referer) {
                $request->headers->set('referer', $referer);
            }
            $redirect = redirect($destination);
            $expected = $redirect->getTargetUrl();
            $response = (new PreserveListQuery)->handle($request, fn () => $redirect);

            $this->assertSame($expected, $response->getTargetUrl());
        }
    }
}
