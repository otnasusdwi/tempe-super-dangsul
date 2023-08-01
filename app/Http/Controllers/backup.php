<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\Harga;
use App\Models\Tipe;
use App\Models\Laporan;
use App\Models\ItemLaporan;
use DB;
use Carbon\Carbon;
use Auth;

class SalesHomeController extends Controller
{
	public function index()
	{
		$harga = DB::table('harga')
		->orderBy('harga', 'desc')
		->where('id_tipe', Auth::user()->tipe)
		->get();
		
		return view('sales.home')->with(['harga' => $harga]);
	}
	
	public function input(Request $request)
	{	
		$sisa =[];
		$jumlah = [];
		$total = 0;
		$bawa = $request->bawa;
		$sisa_muda = $request->sisa_muda;
		$sisa_tua = $request->sisa_tua;
		$harga = $request->harga;
		$hutang_baru = $request->hutang_baru;
		$pelunasan = $request->pelunasan;
		$tgl_laporan = $request->tgl_laporan;

		echo "<pre>";
		print_r($harga);
		echo "<br>";
		print_r($bawa);
		echo "<br>";
		print_r($sisa_muda);
		echo "<br>";
		print_r($sisa_tua);
		echo "<br>";
		print_r($hutang_baru);
		echo "<br>";
		print_r($pelunasan);

		die();

		$tgl = DB::table('laporan')
		->where('id_user', Auth::user()->id)
		->where('tgl_laporan', $tgl_laporan)
		->first();
		
		$pesan_tgl = 'Anda Sudah Laporan di Tanggal ' . $tgl_laporan;
		
		if ($tgl) {
			return redirect()->route('sales.home')->with(['tgl' => $pesan_tgl]);
		}else{
			for ($i = 0; $i < count($bawa); $i++) {
				if ($bawa[$i] == NULL) {
					$bawa[$i] = 0;
				}
				
				if ($laku[$i] == NULL) {
					$laku[$i] = 0;
				} 
				
				if ($laku[$i] > $bawa[$i]) {
					return redirect()->route('sales.home')->with(['laku' => 'Jumlah Laku Tidak Boleh Lebih Dari Jumlah Bawa']);
				}
				
				$sisa[$i] = $bawa[$i] - $laku[$i];
				$jumlah[$i] = $laku[$i] * $harga[$i];
			}
			
			$total_sisa = 0;
			for ($i=0; $i < count($sisa); $i++) { 
				$total_sisa = $total_sisa + $sisa[$i];
			}
			
			if ($total_sisa == 0) {
				
				$total = 0;
				for ($i=0; $i < count($jumlah); $i++) { 
					$total = $total + $jumlah[$i];
				}
				
				$id_laporan = Uuid::uuid4()->getHex();
				
				for ($i=0; $i < count($sisa); $i++) { 
					$sisa_tua[$i] = 0; 
					$sisa_muda[$i] = 0; 
				}
				
				$tipe = DB::table('tipe')
				->where('id_tipe', Auth::user()->tipe)
				->first();
				
				if ($hutang_baru) {
					$piutang = Auth::user()->piutang + $hutang_baru;
					DB::table('users')->where('id', Auth::user()->id)->update([ 'piutang' => $piutang ]);
				}elseif ($pelunasan){
					$piutang = Auth::user()->piutang - $pelunasan;
					DB::table('users')->where('id', Auth::user()->id)->update(['piutang' => $piutang ]);
				}else{
					$piutang = Auth::user()->piutang;
				}
				// dd($piutang);
				if ($tipe->margin == 1) {
					$marginsales = ($total*10)/100;
					$setoran = ($total - $marginsales) + $piutang;
				}
				if ($tipe->margin == 0){
					$marginsales = 0;
					$setoran = $total + $piutang;
				}

				// dd($setoran);
				
				$user = DB::table('users')
				->where('id', Auth::user()->tipe)
				->first();
				
				if ($hutang_baru) {
					$setoran = $setoran - $user->piutang;
				}
				
				$jam = Carbon::now()->format('H:i:s');
				$tgl = $tgl_laporan.' '.$jam;
				
				$laporan = new Laporan;
				$laporan->id_laporan = $id_laporan;
				$laporan->id_user = Auth::user()->id;
				$laporan->tgl_laporan = $tgl;
				$laporan->hutang_baru = $hutang_baru;
				$laporan->pelunasan = $pelunasan;
				$laporan->total = $total;
				$laporan->marginsales = $marginsales;
				$laporan->setoran = $setoran;
				$laporan->piutang = $piutang;
				$laporan->save();
				
				for ($i = 0; $i < count($harga); $i++) {
					$item = new ItemLaporan;
					$item->id_laporan = $id_laporan;
					$item->harga = $harga[$i];
					$item->bawa = $bawa[$i];
					$item->laku = $laku[$i];
					$item->sisa_muda = $sisa_muda[$i];
					$item->sisa_tua = $sisa_tua[$i];
					$item->save();
				}
				
				return view('sales.hasil', ['harga' => $harga, 'bawa' => $bawa , 'laku' => $laku , 'sisa_muda' => $sisa_muda ,'sisa_tua' => $sisa_tua , 'jumlah' => $jumlah, 'total' => $total , 'setoran' => $setoran , 'pelunasan' => $pelunasan , 'piutang' => $piutang , 'hutang_baru' => $hutang_baru,  'tgl_laporan' => $tgl_laporan, 'marginsales' => $marginsales]);
			}else{
				return view('sales.sisa', ['pelunasan' => $pelunasan, 'hutang_baru' => $hutang_baru, 'tgl_laporan' => $tgl_laporan, 'harga' => $harga, 'bawa' => $bawa , 'laku' => $laku , 'sisa' => $sisa , 'jumlah' => $jumlah]);
			}
		}
	}	
	
	public function sisa(Request $request)
	{	
		$sisa_tua = [];
		$sisa = $request->sisa;
		$sisa_muda = $request->sisa_muda;
		$jumlah = $request->jumlah;
		$bawa = $request->bawa;
		$laku = $request->laku;
		$harga = $request->harga;
		$hutang_baru = $request->hutang_baru;
		$pelunasan = $request->pelunasan;
		$tgl_laporan = $request->tgl_laporan;
		$total = 0;
		
		for ($i=0; $i < count($jumlah); $i++) { 
			$total = $total + $jumlah[$i];
		}
		
		// dd($pelunasan);
		
		$id_laporan = Uuid::uuid4()->getHex();
		
		for ($i=0; $i < count($sisa); $i++) { 
			$sisa_tua[$i] = $sisa[$i] - $sisa_muda[$i]; 
		}
		
		$tipe = DB::table('tipe')
		->where('id_tipe', Auth::user()->tipe)
		->first();
		
		if ($hutang_baru) {
			$piutang = Auth::user()->piutang + $hutang_baru;
			DB::table('users')->where('id', Auth::user()->id)->update([ 'piutang' => $piutang ]);
		}elseif ($pelunasan){
			$piutang = Auth::user()->piutang - $pelunasan;
			DB::table('users')->where('id', Auth::user()->id)->update(['piutang' => $piutang ]);
		}else{
			$piutang = Auth::user()->piutang;
		}
		// dd($piutang);
		if ($tipe->margin == 1) {
			$marginsales = ($total*10)/100;
			$setoran = ($total - $marginsales) + $piutang;
		}
		
		if ($tipe->margin == 0){
			$marginsales = 0;
			$setoran = $total + $piutang;
		}
		
		$user = DB::table('users')
		->where('id', Auth::user()->tipe)
		->first();
		
		if ($hutang_baru) {
			$setoran = $setoran - $user->piutang;
		}
		
		$jam = Carbon::now()->format('H:i:s');
		$tgl = $tgl_laporan.' '.$jam;
		
		$laporan = new Laporan;
		$laporan->id_laporan = $id_laporan;
		$laporan->id_user = Auth::user()->id;
		$laporan->tgl_laporan = $tgl;
		$laporan->hutang_baru = $hutang_baru;
		$laporan->pelunasan = $pelunasan;
		$laporan->total = $total;
		$laporan->marginsales = $marginsales;
		$laporan->setoran = $setoran;
		$laporan->piutang = $piutang;
		$laporan->save();
		
		for ($i = 0; $i < count($harga); $i++) {
			$item = new ItemLaporan;
			$item->id_laporan = $id_laporan;
			$item->harga = $harga[$i];
			$item->bawa = $bawa[$i];
			$item->laku = $laku[$i];
			$item->sisa_muda = $sisa_muda[$i];
			$item->sisa_tua = $sisa_tua[$i];
			$item->save();
		}
		
		return view('sales.hasil', ['harga' => $harga, 'bawa' => $bawa , 'laku' => $laku , 'sisa_muda' => $sisa_muda ,'sisa_tua' => $sisa_tua , 'jumlah' => $jumlah, 'total' => $total , 'setoran' => $setoran , 'pelunasan' => $pelunasan , 'piutang' => $piutang , 'hutang_baru' => $hutang_baru,  'tgl_laporan' => $tgl_laporan, 'marginsales' => $marginsales]);
	}
}