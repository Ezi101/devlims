<div class="row">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-3">
				<div class="box box-solid box-warning">
					<div class="box-header with-border">
						<h4 class="box-title">
							@lang('project::lang.incompleted_tasks')
						</h4>
						<!-- /.box-tools -->
					</div>
					<!-- /.box-header -->
					<div class="box-body text-center">
						<span class="fs-20">
							<b>{{$project->incomplete_task}}</b>
						</span>
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->
			</div>
			@if(isset($project->settings['enable_notes_documents']) && $project->settings['enable_notes_documents'])
				<div class="col-md-3">
					<div class="box box-solid box-primary">
						<div class="box-header with-border">
							<h4 class="box-title">
								@lang('project::lang.documents_and_notes')
							</h4>
							<!-- /.box-tools -->
						</div>
						<!-- /.box-header -->
						<div class="box-body text-center">
							<span class="fs-20">
								<b>{{$project->note_and_documents_count}}</b>
							</span>
						</div>
						<!-- /.box-body -->
					</div>
					<!-- /.box -->
				</div>
			@endif
			@if(isset($project->settings['enable_timelog']) && $project->settings['enable_timelog'])
				<div class="col-md-3">
					<div class="box box-solid box-info">
						<div class="box-header with-border">
							<h4 class="box-title">
								@lang('project::lang.total_time')
							</h4>
							<!-- /.box-tools -->
						</div>
						<!-- /.box-header -->
						<div class="box-body text-center">
							@php
								$hours = floor($timelog->total_seconds / 3600);
								$minutes = floor(($timelog->total_seconds / 60) % 60);
							@endphp
							<span>
								<b>
									{{sprintf('%02d:%02d', $hours, $minutes)}}
								</b>
							</span>
						</div>
						<!-- /.box-body -->
					</div>
					<!-- /.box -->
				</div>
			@endif
			{{-- @if((isset($project->settings['enable_invoice']) && $project->settings['enable_invoice']) && $is_lead_or_admin)
				<div class="col-md-3">
					<div class="box box-solid box-success">
						<div class="box-header with-border">
							<h4 class="box-title">
								@lang('sale.total_paid')
								<small class="text-white">
									@lang('project::lang.invoice')
								</small>
							</h4>
							<!-- /.box-tools -->
						</div>
						<!-- /.box-header -->
						<div class="box-body text-center">
							<span>
								<b>
									<span class="subtotal display_currency" data-currency_symbol="true">
										{{$invoice->paid}}
									</span>
								</b>
							</span>
						</div>
						<!-- /.box-body -->
					</div>
					<!-- /.box -->
				</div>
			@endif
			@if((isset($project->settings['enable_invoice']) && $project->settings['enable_invoice']) && $is_lead_or_admin)
				<div class="col-md-3">
					<div class="box box-solid box-danger">
						<div class="box-header with-border">
							<h4 class="box-title">
								@lang('sale.total_remaining')
								<small class="text-white">
									@lang('project::lang.invoice')
								</small>
							</h4>
							<!-- /.box-tools -->
						</div>
						<!-- /.box-header -->
						<div class="box-body text-center">
							<span>
								<b>
									<span class="subtotal display_currency" data-currency_symbol="true">
										{{$transaction->total - $invoice->paid}}
									</span>
								</b>
							</span>
						</div>
						<!-- /.box-body -->
					</div>
					<!-- /.box -->
				</div>
			@endif --}}
		</div>
		
	</div>
	<div class="col-md-12">
		<!-- customer -->
		<div class="box box-solid box-default">
			<div class="box-header with-border">
				<h4 class="box-title">
					<i class="fas fa-check-circle"></i>
					Information
				</h4>
			</div>
			<div class="box-body">
				<div class="col-md-8">
					<i class="fa fa-flask"></i>
					<span> Sample :- </span>
					@if(isset($project->product->name))
						{{$project->product->name}}
					@endif 
				</div>

				{{-- <div class="col-md-4">
					<i class="fa fa-briefcase"></i><span> Customer :- </span>
					@if(isset($project->customer->name))
						{{$project->customer->name}}
					@endif 
				</div> --}}

				{{-- <div class="col-md-8">
					<i class="fa fa-map-marker"></i>
					<span> @lang('business.address') :- </span>
					@if(isset($project->customer->landmark))
						{{ $project->customer->landmark }}
					@endif

					@if(isset($project->customer->city))
						{{ ', ' . $project->customer->city }}
					@endif

					@if(isset($project->customer->state))
						{{ ', ' . $project->customer->state }}
					@endif
					@if(isset($project->customer->country))
						{{ ', ' . $project->customer->country }}
					@endif
				</div> --}}

				{{-- <div class="col-md-4">
					<i class="fa fa-mobile"></i>
					@lang('contact.mobile') :-
					@if(isset($project->customer->mobile))
						{{$project->customer->mobile}} 
					@endif
				</div> --}}
					
				<div class="col-md-8">
					<i class="fas fa-check-circle"></i>
					@lang('sale.status'):
					@lang('project::lang.'.$project->status)
				</div>

				<div class="col-md-4">
					@if($project->categories->count() > 0)
						<i class="fa fas fa-gem"></i>
						@lang('category.categories') :-
						<span>
						@foreach($project->categories as $categories)
							@if(!$loop->last)
								{{$categories->name . ','}}
							@else
								{{$categories->name}}
							@endif
						@endforeach
						</span>
					@endif
				</div>

				<div class="col-md-8">
					<i class="fa fa-clock"></i> @lang('project::lang.start_date_time') :-
					@if (isset($project->start_date))
					{{ @format_datetime($project->start_date) }}
				@endif
				
				</div>
				<div class="col-md-4">
					
				</div>

				<div class="col-md-8">
					<i class="fa fa-clock"></i> @lang('project::lang.end_date_time') :-
					@if (isset($project->end_date))
					{{ @format_datetime($project->end_date) }}
					@endif
				</div>
				<div class="col-md-4">
					<i class="fa fa-user"></i> @lang('project::lang.lead') :-
					@if (isset($project->lead->user_full_name))
					{{ $project->lead->user_full_name }}
					@endif
				</div>
				<div class="col-md-12">
					@if(!empty($project->description))
	
					<div class="box box-solid box-default" style="margin-top: 1%;">
						<div class="box-header with-border" style="padding: 1%; ">
							<h5 class="box-title">
								<i class="fa fa-file"></i> @lang('project::lang.projet_decription')
							</h5>
						</div>
						<div class="box-body" style="padding: 0%">
							@if(!empty($project->description))
								&emsp;{!! $project->description !!}
							@endif
						</div>
					</div>
					
					@endif
				</div>
			</div>
			<!-- /.box-body -->
			<div class="box-footer" style="padding: 10px 38px">
				@includeIf('project::avatar.create', ['max_count' => '10', 'members' => $project->members])
			</div>
			<!-- /.box-footer-->
		</div>
	</div>
</div>