<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use Exception;
use Log;
use Bugsnag;
use Illuminate\Http\Request;

class ExtendedBaseInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', ['except' => ['pdf']]);
    }

    public function newPayment(Request $request)
    {
        try {
            $clientid = $request->input('clientid');
            $invoice = new Invoice();
            $order = new Order();
            $invoices = $invoice->where('user_id', $clientid)->where('status', '=', 'pending')->orderBy('created_at', 'desc')->get();
            $cltCont = new \App\Http\Controllers\User\ClientController();
            $invoiceSum = $cltCont->getTotalInvoice($invoices);
            $amountReceived = $cltCont->getAmountPaid($clientid);
            $pendingAmount = $invoiceSum - $amountReceived;
            $client = $this->user->where('id', $clientid)->first();
            $currency = $client->currency;
            $orders = $order->where('client', $clientid)->get();

            return view('themes.default1.invoice.newpayment', compact('clientid', 'client', 'invoices',  'orders',
                  'invoiceSum', 'amountReceived', 'pendingAmount', 'currency'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postNewPayment($clientid, Request $request)
    {
        dd('sad');
        $this->validate($request, [
           'payment_date'  => 'required',
           'payment_method'=> 'required',
           'amount'        => 'required',
        ]);

        try {
            $payment = new Payment();
            $payment->payment_status = 'success';
            $payment->user_id = $clientid;
            $payment->invoice_id = '--';
            $paymentReceived = $payment->fill($request->all())->save();

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function edit($invoiceid, Request $request)
    {

        $invoice = Invoice::where('id',$invoiceid)->first();
        return view('themes.default1.invoice.editInvoice',compact('userid','invoiceid','invoice'));

    }

    public function postEdit($invoiceid, Request $request)
    {
        $this->validate($request, [
        'total' => 'required',
        'status'=> 'required',
        ]);

        try {
            $total = $request->input('total');
            $status = $request->input('status');
            $invoice = Invoice::where('id', $invoiceid)->update(['grand_total'=>$total, 'status'=>$status]);

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postNewMultiplePayment($clientid , Request $request)
    {
        try {
            $payment_date = $request->payment_date;
            $payment_method = $request->payment_method;
            $totalAmt=$request->totalAmt;
            $invoiceChecked = $request->invoiceChecked;
            $invoicAmount = $request->invoiceAmount;
            $amtToCredit = $request->amtToCredit;
            $payment_status= "success";
            $payment = $this->multiplePayment($clientid,$invoiceChecked, $payment_method,
             $payment_date, $totalAmt,$invoicAmount,$amtToCredit,$payment_status);
            $response = ['type' => 'success', 'message' => 'Payment Updated Successfully', ];
               return response()->json($response);
        } catch (\Exception $ex) {
            app('log')->useDailyFiles(storage_path().'/logs/laravel.log');
            app('log')->error($ex->getMessage());
            Bugsnag::notifyException($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }

    }

    public function multiplePayment($clientid,$invoiceChecked, $payment_method,
             $payment_date, $totalAmt,$invoicAmount,$amtToCredit,$payment_status)
    {
       try {
        foreach ($invoiceChecked as $key => $value) {
            dd($key);
            if($key != 0){//If Payment is linked to Invoice
        $invoice = Invoice::find($value);
        $invoice_status = 'pending';
        $payment = Payment::where('invoice_id',$value)->create([
                'invoice_id'     => $value,
                'user_id'       => $clientid,
                'amount'         =>$invoicAmount[$key],
                'payment_method' => $payment_method,
                'payment_status' => $payment_status,
                'created_at'     => $payment_date,
            ]);
            $totalPayments = $this->payment
            ->where('invoice_id', $value)
            ->where('payment_status', 'success')
            ->pluck('amount')->toArray();
            $total_paid = array_sum($totalPayments);
            if ($total_paid >= $invoice->grand_total) {
                $invoice_status = 'success';
            }
            if ($invoice) {
                $invoice->status = $invoice_status;
                $invoice->save();
            }
        }
        else{//If Payment is not linked to any invoice and is to be credited to User Accunt
            $payment = Payment::create([
                'invoice_id'     => $value,
                'user_id'       => $clientid,
                'amount'         =>$totalAmt,
                'payment_method' => $payment_method,
                'payment_status' => $payment_status,
                'created_at'     => $payment_date,
            ]);
        }

    }
    return $payment;
           
       } catch (Exception $e) {
            app('log')->useDailyFiles(storage_path().'/logs/laravel.log');
            app('log')->error($ex->getMessage());
            Bugsnag::notifyException($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
       }
    }

    public function updateNewMultiplePayment($clientid , Request $request)
    {
        try {
            $payment_date = $request->payment_date;
            $payment_method = $request->payment_method;
            $totalAmt=$request->totalAmt;
            $invoiceChecked = $request->invoiceChecked;
            $invoicAmount = $request->invoiceAmount;
            $amtToCredit = $request->amtToCredit;
            $payment_status= "success";
            $payment = $this->multiplePayment($clientid,$invoiceChecked, $payment_method,
             $payment_date, $totalAmt,$invoicAmount,$amtToCredit,$payment_status);
            $response = ['type' => 'success', 'message' => 'Payment Updated Successfully', ];
               return response()->json($response);
        } catch (\Exception $ex) {
            app('log')->useDailyFiles(storage_path().'/logs/laravel.log');
            app('log')->error($ex->getMessage());
            Bugsnag::notifyException($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }

    }
}
