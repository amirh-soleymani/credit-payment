<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendRequest;
use App\Http\Requests\UpdateCreditRequestStatus;
use App\Http\Resources\CreditRequestResource;
use App\Models\CreditRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CreditRequestController extends Controller
{
    public function sendRequest(SendRequest $sendRequest)
    {
        $sellerId = $sendRequest->input('seller_id');
        $clientId = auth()->guard('api')->user()->id;

        $checkCreditRequest = CreditRequest::where([
            ['seller_id', $sellerId],
            ['client_id', $clientId]
        ])->first();
        if (!is_null($checkCreditRequest)) {
            return Response::errorResponse('You Have made Credit Request for This Seller Before', [], 400);
        }

        $creditRequest = CreditRequest::create([
            'seller_id' => $sellerId,
            'client_id' => $clientId,
        ]);

        return Response::successResponse('Your Credit Request Registered Successfully', CreditRequestResource::make($creditRequest));
    }

    public function sellerCreditRequestList(Request $request)
    {
        $seller = auth()->guard('api')->user();
        if ($seller->type != User::SELLER){

            return Response::errorResponse('Access Denied', [], 403);
        }

        $sellerCreditRequests = CreditRequest::where('seller_id', $seller->id)
            ->get();

        return Response::successResponse('Done', CreditRequestResource::collection($sellerCreditRequests));
    }

    public function checkClientCredit(UpdateCreditRequestStatus $updateCreditRequestStatus)
    {
        $creditRequest = CreditRequest::find($updateCreditRequestStatus->credit_request_id);
        $clientCredit = $creditRequest->client->creditScore;

        if ($clientCredit >= 10) {
            return Response::successResponse('Client Has Enough Score For Credit Payment',[]);
        }

        return Response::successResponse('Client Doesnt have Enough Score For Credit Payment',[]);
    }

    public function clientCreditRequestList(Request $request)
    {
        $client = auth()->guard('api')->user();
        if ($client->type != User::CLIENT){

            return Response::errorResponse('Access Denied', [], 403);
        }

        $clientCreditRequests = CreditRequest::where('client_id', $client->id)
            ->get();

        return Response::successResponse('Done', CreditRequestResource::collection($clientCreditRequests));
    }

    public function sellerAcceptCreditRequest(UpdateCreditRequestStatus $updateCreditRequestStatus)
    {
        $client = auth()->guard('api')->user();
        if ($client->type != User::SELLER){

            return Response::errorResponse('Access Denied', [], 403);
        }

        $creditRequest = CreditRequest::find($updateCreditRequestStatus->credit_request_id);
        if ($creditRequest->seller_id != $client->id) {
            return Response::errorResponse('Access Denied', [], 403);
        }

        $creditRequest->status = CreditRequest::ACCEPT;
        $creditRequest->save();

        return Response::successResponse('Credit Request Accepted Successfully.', CreditRequestResource::make($creditRequest));
    }

    public function sellerDenyCreditRequest(UpdateCreditRequestStatus $updateCreditRequestStatus)
    {
        $creditRequest = CreditRequest::find($updateCreditRequestStatus->credit_request_id);
        $creditRequest->status = CreditRequest::DENY;
        $creditRequest->save();

        return Response::successResponse('Credit Request Denied Successfully', CreditRequestResource::make($creditRequest));
    }
}
