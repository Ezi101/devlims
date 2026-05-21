<table class="table ajax_view hide-footer" id="purchase_table" style="width: 100%;">
    <thead>
        <tr>
            {{-- <th>@lang('messages.action')</th> --}}
            <th>@lang('product.product')</th>
            <th>@lang('report.generic')</th>
            <th>@lang('batch.b_no')</th>
            <th>@lang('report.supplier_name')</th>
            <th id="instalments">@lang('report.instalments')</th>
            {{-- <th>@lang('purchase.ref_no')</th>
            <th>@lang('purchase.mfg_date')</th>
            <th>@lang('purchase.exp_date')</th> --}}
            <th>@lang('batch.b_quantity') </th>
            <th>@lang('messages.date')</th>
            {{-- <th>@lang('purchase.location')</th> --}}
            {{-- <th>@lang('purchase.supplier')</th> --}}
            {{-- <th>@lang('purchase.purchase_status')</th> --}}
            {{-- <th>@lang('batch.i_status')</th> --}}
            {{-- <th>@lang('purchase.payment_status')</th>
            <th>@lang('purchase.grand_total')</th>
            <th>@lang('purchase.payment_due') &nbsp;&nbsp;<i class="fa fa-info-circle text-info no-print" data-toggle="tooltip" data-placement="bottom" data-html="true" data-original-title="{{ __('messages.purchase_due_tooltip')}}" aria-hidden="true"></i></th> --}}
            {{-- <th>@lang('lang_v1.added_by')</th> --}}
        </tr>
    </thead>
    {{-- <tfoot>
        <tr class="bg-gray font-17 text-center footer-total">
            <td colspan="5"><strong>@lang('sale.total'):</strong></td>
            <td class="footer_status_count"></td>
            <td class="footer_payment_status_count"></td>
            <td class="footer_purchase_total"></td>
            <td class="text-left"><small>@lang('report.purchase_due') - <span class="footer_total_due"></span><br>
            @lang('lang_v1.purchase_return') - <span class="footer_total_purchase_return_due"></span>
            </small></td>
            <td></td>
        </tr>
    </tfoot> --}}
</table>
