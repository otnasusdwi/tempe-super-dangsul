@extends('../layouts.app')
@section('content')
<!-- [ Main Content ] start -->
<div class="pcoded-main-container">
	<div class="pcoded-wrapper">
		<div class="pcoded-content">
			<div class="pcoded-inner-content">
				<!-- [ breadcrumb ] start -->
				<div class="page-header">
					<div class="page-block">
						<div class="row align-items-center">
							<div class="col-md-12">
								<div class="page-header-title">
									<h5 class="m-b-10">Monitoring</h5>
								</div>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
									<li class="breadcrumb-item"><a href="{{ route('admin.detail_monitoring', ['id_monitoring' => $id_monitoring])}}">Data Monitoring</a></li>
									<li class="breadcrumb-item"><a href="javascript:">Sisa</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!-- [ breadcrumb ] end -->
				<div class="main-body">
					<div class="page-wrapper">
						<!-- [ Main Content ] start -->
						<div class="row">
							<!--[ Recent Users ] start-->
							<div class="col-xl-12 col-md-12">
								<form method="POST" action="{{ route('admin.store_sisa_monitoring') }}">
									@csrf
									<input type="hidden" name="id_monitoring" value="{{$id_monitoring}}">
									<input type="hidden" name="tgl_laporan" value="{{$tgl_laporan}}">
									<div class="card Recent-Users">
										<div class="card-header">
											<div class="row">
												<div class="col-xl-6 col-md-6">
													<h5>Tanggal Laporan : {{ $tgl}}</h5>
													{{-- <a href="{{ route('admin.edit_monitoring', $id_monitoring) }}" title="" class="btn theme-bg" style="color: white;">Edit</a> --}}
												</div>

											</div>
										</div>
										<div class="card-block px-0 py-3">
											<div class="table-responsive">
												{{-- <input type="hidden" name="tgl_laporan" value="{{ $tgl_laporan }}"> --}}
												{{-- <input type="hidden" name="id_tipe" value="{{ $id_tipe }}"> --}}
												<table class="table table-hover text-center" id="">
													<thead>
														<tr class="unread">   
															<th rowspan="2">
																<h6 class="mb-1"><b>NO</b></h6>
															</th> 
															<th rowspan="2">
																<h6 class="mb-1"><b>SALES</b></h6>
															</th> 
															<th colspan="4">
																<h6 class="mb-1"><b>SISA MUDA</b></h6>
															</th> 
															<th colspan="4">
																<h6 class="mb-1"><b>SISA TUA</b></h6>
															</th> 
														</tr> 
														<tr class="unread">     
															@foreach ($harga as $hrg)
															<th>
																<h6 class="mb-1"><b>{{ $hrg->harga }}</b></h6>
															</th>
															@endforeach 
															@foreach ($harga as $hrg)
															<th>
																<h6 class="mb-1"><b>{{ $hrg->harga }}</b></h6>
															</th>
															@endforeach    
														</tr>
													</thead>
													<tbody>
														@foreach ($harga as $hrg)
														<input type="hidden" name="harga[]" value="{{$hrg->harga}}">
														@endforeach  
														@foreach($users as $index => $row)
														<input type="hidden" name="users[{{$index}}]" value="{{$row->id}}">
														<tr>
															<td>
																<h6 class="mb-1">{{ $index+1 }}</h6>
															</td> 
															<td>
																<h6 class="mb-1">{{$row->name}}</h6>
															</td> 
															@foreach ($harga as $hrg)
															@foreach ($item_monitoring as $itm)
															@if ($itm->id_user == $row->id && $itm->harga == $hrg->harga && $itm->id_monitoring == $id_monitoring)
															@php
															$md = $itm->muda;
															@endphp
															@endif
															@endforeach
															<td>
																<h6 class="mb-1">
																	<input type="number" class="form-control" placeholder=""
																	name="muda[{{$index}}][]" min="0" required value="{{$md}}">
																</h6>
															</td>
															@endforeach
															@foreach ($harga as $hrg)
															@foreach ($item_monitoring as $itm)
															@if ($itm->id_user == $row->id && $itm->harga == $hrg->harga && $itm->id_monitoring == $id_monitoring)
															@php
															$t = $itm->tua;
															@endphp
															@endif
															@endforeach
															<td>
																<h6 class="mb-1">
																	<input type="number" class="form-control" placeholder=""
																	name="tua[{{$index}}][]" min="0" required value="{{$t}}">
																</h6>
															</td>
															@endforeach
														</tr>
														@endforeach     
													</tbody>
												</table>
												<div class="text-center">
													<button type="submit" class="btn theme-bg"
													style="color: white; width: 250px;"><b>SUMBIT</b></button>
												</div>
												
											</div>
										</div>
									</div>
								</form>
							</div>
							<!--[ Recent Users ] end-->
						</div>
						<!-- [ Main Content ] end -->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection