<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .active-item {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
            color: white !important;
        }

        .item-list,
        .item-details {
            max-height: 65vh;
            overflow-y: auto;
        }

        .bg-light-gray {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        }

        .bg-darker-gray {
            background: linear-gradient(45deg, #e9ecef, #dee2e6);
        }

        .fixed-bottom-section {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            background: linear-gradient(45deg, #f1f3f5, #dcdcdc);
        }

        .discount {
            color: red;
        }

        .logout-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            background: transparent;
            border: none;
        }

        .logout-btn i {
            font-size: 1.5rem;
            color: #dc3545;
        }

        .btn-icon {
            width: 3rem;
            height: 3rem;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(45deg, #0d6efd, #007bff);
            border: none;
            border-radius: 5px;
        }

        .btn-voucher {
            background: linear-gradient(45deg, #0d6efd, #0056b3);
        }

        .btn-notes {
            background: linear-gradient(45deg, #6c757d, #5a6268);
        }

        .back-btn {
            width: 50px;
            height: 50px;
            position: absolute;
            bottom: 10px;
            left: 10px;
            border-radius: 5px;
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-btn:hover {
            background: linear-gradient(45deg, #0056b3, #003f88);
            cursor: pointer;
        }

        .table thead th {
            background: linear-gradient(45deg, #ffffff, #f1f3f5);
            border-radius: 5px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .table td {
            vertical-align: middle;
        }

        .table .item-qty {
            text-align: center;
        }

        .cart-table {
            background: linear-gradient(45deg, #ffffff, #f8f9fa);
            border-radius: 5px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            table-layout: fixed;
        }

        .cart-table th,
        .cart-table td {
            padding: 0.75rem;
            text-align: center;
        }

        .cart-table th {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }

        .cart-table .remove-item-btn {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            color: white;
            padding: 0.5rem;
            border-radius: 5px;
        }

        .compact-text {
            margin: 2px 0;
        }

        .highlight {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: linear-gradient(45deg, #ffffff, #f8f9fa);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .dropdown-menu {
            transition: opacity 0.3s ease, transform 0.3s ease;
            transform: translateY(-10px);
            opacity: 0;
            pointer-events: none;
        }

        .dropdown-menu.show {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .toggle-menu {
            transition: all 0.3s ease;
        }

        .toggle-hidden {
            transform: translateX(-100%);
            visibility: hidden;
        }

        .toggle-btn {
            position: absolute;
            top: 10px;
            left: -40px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007bff, #00bfff);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .toggle-btn:hover {
            background: linear-gradient(135deg, #007bff, #00bfff);
        }

        .input-group-text {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .list-group {
            border: 1px solid #ddd;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        input:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .shadow-sm {
            box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
        }

        .btn-outline-danger {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #itemCatalog {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .item-card {
            width: 100%;
            font-weight: bold;
            background: linear-gradient(135deg, #007bff, #00bfff);
            color: white;
            border-radius: 8px;
            padding: 10px;
            transition: transform 0.3s ease;
        }

        .item-card:hover {
            transform: scale(1.05);
        }

        .card-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            margin: 0;
            font-size: 1.2em;
            font-weight: bold;
        }

        .card-text {
            margin: 0;
            font-size: 1.1em;
            font-weight: bold;
        }

        /* Modal Styling */
        .modal-dialog.success-modal {
            max-width: 500px;
            /* Set a maximum width for the modal */
            background: linear-gradient(135deg, #34b7f1, #70c5f0);
            /* Gradient background */
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Modal Content */
        .modal-content {
            background-color: #ffffff;
            /* White background */
            border-radius: 15px;
            padding: 25px;
        }

        /* Modal Body */
        .modal-body {
            color: #333;
            padding-bottom: 20px;
        }

        /* Modal Header (Icon and Title) */
        .modal-body .fa-check-circle {
            color: #28a745;
        }

        /* Title */
        h4 {
            font-size: 1.8rem;
            font-weight: bold;
            color: #28a745;
        }

        /* Text content */
        p {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 10px;
        }

        /* Button Styles */
        .modal-footer button {
            border-radius: 50px;
            /* Rounded buttons */
            font-size: 1rem;
            padding: 10px 20px;
            margin: 5px;
            transition: background-color 0.3s ease;
        }

        /* Button Hover Effects */
        .modal-footer button:hover {
            color: #fff;
        }

        /* Success Button */
        .btn-outline-success {
            border: 2px solid #28a745;
            color: #28a745;
        }

        .btn-outline-success:hover {
            background-color: #28a745;
            color: #fff;
        }

        /* Print Button */
        .btn-info {
            background-color: #17a2b8;
            border: 2px solid #17a2b8;
        }

        .btn-info:hover {
            background-color: #138496;
            border-color: #138496;
        }

        /* Large Buttons */
        .btn-lg {
            font-size: 1.1rem;
            padding: 12px 25px;
        }

        /* Total Section */
        .fixed-bottom-section .fs-4 {
            font-size: 1.25rem;
        }

        .fixed-bottom-section .text-primary {
            color: #007bff;
            /* Blue color for 'Total' */
        }

        /* Discount Section */
        #discountSection .text-danger {
            color: #e74c3c;
            /* Red color for Discount */
        }

        /* Action Buttons */
        .btn-icon {
            border-radius: 50%;
            /* Circular buttons */
            padding: 12px;
            font-size: 1.5rem;
            width: 48px;
            height: 48px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-icon:hover {
            background-color: #007bff;
            color: white;
            transform: scale(1.1);
            /* Hover scale effect */
        }

        /* Pay Button */
        .btn-gradient-blue {
            background: linear-gradient(135deg, #007bff, #00bfff);
            /* Blue gradient */
            color: white;
            border: none;
        }

        .btn-gradient-blue:hover {
            background: linear-gradient(135deg, #0056b3, #0099cc);
            /* Darker blue gradient on hover */
            color: white;
        }

        /* Modal Content Styling */
        .modal-content {
            background-color: #fff;
            /* White background for modal */
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            /* Light shadow for depth */
        }

        .modal-header {
            background-color: #007bff;
            /* Blue background for the header */
            color: white;
            /* White text */
            border-bottom: 1px solid #ddd;
            /* Subtle bottom border */
        }

        .modal-title {
            font-weight: bold;
        }

        .modal-body {
            padding: 20px;
        }

        /* Payment Methods List */
        .list-group {
            margin-top: 10px;
        }

        .payment-method {
            background-color: #f8f9fa;
            /* Light background color for buttons */
            border-radius: 10px;
            padding: 15px;
            font-size: 1.1rem;
            color: #333;
            /* Dark text for visibility */
            transition: background-color 0.3s, transform 0.3s ease;
        }

        .payment-method i {
            font-size: 1.5rem;
            color: #007bff;
            /* Blue icon color */
        }

        .payment-method:hover {
            background-color: #007bff;
            /* Blue background on hover */
            color: white;
            /* White text on hover */
            transform: scale(1.05);
            /* Slight scale effect on hover */
        }

        /* Close Button Styling */
        .btn-close {
            color: white;
            opacity: 1;
        }

        .btn-close:hover {
            opacity: 0.7;
        }


        /* Responsiveness */
        @media (max-width: 768px) {
            .fixed-bottom-section {
                padding: 10px;
            }

            .btn-icon {
                padding: 10px;
                font-size: 1.2rem;
            }

            .btn-gradient {
                font-size: 1.1rem;
                padding: 12px;
            }

            .text-primary {
                font-size: 1.1rem;
            }

            .fs-5 {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 768px) {
            .table thead {
                display: none;
            }

            .table td {
                display: block;
                width: 100%;
                text-align: right;
            }

            .table td::before {
                content: attr(data-label);
                font-weight: bold;
                text-transform: uppercase;
                float: left;
            }
        }

        .modal-dialog {
            max-width: 40%;
        }

        @media (max-width: 768px) {
            .modal-dialog {
                max-width: 100%;
            }
        }

        .success-modal {
            background: linear-gradient(45deg, rgba(0, 123, 255, 0.9), rgba(0, 103, 210, 0.9));
            color: white;
        }
    </style>

</head>

<body class="bg-light-gray">

    <div class="container-fluid vh-100 d-flex flex-column">
        <div class="row flex-grow-1 h-100">
            <!-- Left Section -->
            <div class="col-lg-4 col-md-6 border-end p-3 bg-light position-relative d-flex flex-column">
                <!-- Row for Search Bars -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <!-- Search Customer -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white border-0 shadow-sm">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" id="searchCustomer" name="searchCustomer"
                                class="form-control shadow-sm" placeholder="Search by customer name or ID">

                        </div>
                        <div id="customerList"
                            class="list-group customer-list shadow-sm rounded overflow-auto mt-2 position-absolute w-100 bg-white"
                            style="max-height: 200px; z-index: 1050;">
                            <!-- Dynamic search results for customers -->
                        </div>
                    </div>

                    <!-- {{__('Search Item')}} -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white border-0 shadow-sm">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="searchItem" name="searchItem" class="form-control shadow-sm"
                                placeholder="Search by barcode or item name">
                        </div>
                        <div id="itemList"
                            class="list-group item-list shadow-sm rounded overflow-auto mt-2 position-absolute w-100 bg-white"
                            style="max-height: 200px; z-index: 1050;">
                            <!-- Dynamic search results for items -->
                        </div>
                    </div>
                </div>

                <!-- Item Catalog (Item Cards) -->
                <div id="itemCatalog" class="d-flex flex-wrap gap-3 mt-4">
                    <!-- Sample Item Card (to be generated dynamically) -->
                </div>
            </div>

            <!-- POS Details Section -->
            <div class="col-lg-8 col-md-6 d-flex flex-column position-relative bg-darker-gray">

                <!-- POS Details Section -->
                <div class="col-lg-8 col-md-6 d-flex flex-column position-relative bg-darker-gray">
                    <div class="d-flex position-absolute top-0 start-0 mt-3 ms-3 gap-3">
                        <!-- Back Button -->
                        <button class="btn btn-icon shadow-sm rounded-2 bg-primary text-white" id="backBtn"
                            title="Kembali" style="width: 50px; height: 50px;">
                            <i class="fas fa-arrow-left"></i>
                        </button>

                        <!-- Fullscreen Button -->
                        <button class="btn btn-icon shadow-sm rounded-2 bg-info text-white" id="fullscreenBtn"
                            title="Fullscreen" style="width: 50px; height: 50px;">
                            <i class="fas fa-expand"></i>
                        </button>

                        <!-- Cart Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-icon shadow-sm rounded-2 bg-success text-white" id="cartBtn"
                                title="Cart" data-bs-toggle="dropdown" aria-expanded="false"
                                style="width: 50px; height: 50px;">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="cartBtn">
                                <li><a class="dropdown-item" href="#" id="closeRegisterBtn">Close Register</a>
                                </li>
                                <li><a class="dropdown-item" href="#" id="registerDetailBtn">Register Detail</a>
                                </li>
                            </ul>
                        </div>

                        <!-- Hold Orders Button -->
                        <button class="btn btn-icon shadow-sm rounded-2 bg-warning text-white" id="holdOrdersBtn"
                            title="View Hold Orders" style="width: 50px; height: 50px;" data-bs-toggle="modal"
                            data-bs-target="#holdOrdersModal">
                            <i class="fas fa-clipboard-list"></i>
                        </button>
                    </div>
                </div>


                <!-- Modal Hold -->
                <div class="modal fade" id="holdOrdersModal" tabindex="-1" aria-labelledby="holdOrdersModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="holdOrdersModalLabel">Held Orders</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Date</th>
                                            <th>Reference ID</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="holdOrdersList">
                                        <!-- Data will be filled by JavaScript -->
                                    </tbody>
                                </table>
                                <div id="pagination" class="d-flex justify-content-center mt-3">
                                    <!-- Pagination buttons will be dynamically created by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal for Close Register -->
                <div class="modal fade" id="closeRegisterModal" tabindex="-1"
                    aria-labelledby="closeRegisterModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="closeRegisterModalLabel">Close Register</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Table for Register Details -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-striped">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-center">Payment Method</th>
                                                <th class="text-center">Total Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Loop through register details and display payment methods and amounts -->
                                            @foreach ($totalAmountByPaymentMethod as $payment)
                                                <tr>
                                                    <td class="text-center">
                                                        {{ $payment->payment_name ?? 'No payment method available' }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ number_format($payment->total_amount, 2) ?? '0.00' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Table for Total Sales, Total Refund, Total Payment -->
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-striped">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-center">Description</th>
                                                <th class="text-center">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center"><strong>Total Sales</strong></td>
                                                <td class="text-center">${{ number_format($totalSales, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center"><strong>Total Refund</strong></td>
                                                <td class="text-center">${{ number_format($totalRefund, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center"><strong>Total Payment</strong></td>
                                                <td class="text-center">${{ number_format($totalPayment, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Form for Total Cash and Note -->
                                <form id="closeRegisterForm">
                                    <div class="mb-3">
                                        <label for="totalCash" class="form-label">Total Cash</label>
                                        <input type="number" class="form-control" id="totalCash" name="totalCash"
                                            required placeholder="Enter total cash amount">
                                    </div>
                                    <div class="mb-3">
                                        <label for="note" class="form-label">Note</label>
                                        <textarea class="form-control" id="note" name="note" rows="3" placeholder="Optional note"></textarea>
                                    </div>
                                </form>



                                <div class="alert alert-info">
                                    Please confirm the details before closing the register.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="closeRegisterButton">Yes,
                                    Close</button>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Modal for Register Detail -->
                <div class="modal fade" id="registerDetailModal" tabindex="-1"
                    aria-labelledby="registerDetailModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <!-- Modal content with shadow and rounded corners -->
                        <div class="modal-content bg-white shadow-lg rounded">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="registerDetailModalLabel">Register Detail</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Table layout for payment method details -->
                                @if ($totalAmountByPaymentMethod->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-center">Payment Method</th>
                                                    <th class="text-center">Total Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Loop through the totalAmountByPaymentMethod collection -->
                                                @foreach ($totalAmountByPaymentMethod as $payment)
                                                    <tr>
                                                        <td class="text-center">
                                                            {{ $payment->payment_name ?? 'No payment method available' }}
                                                        </td>
                                                        <td class="text-center">
                                                            {{ number_format($payment->total_amount, 2) ?? '0.00' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <!-- Message if no data is available -->
                                    <div class="alert alert-warning text-center">
                                        No payment data available.
                                    </div>
                                @endif

                                <!-- New table for total sales, refund, and payment details with margin -->
                                <div class="mt-4"> <!-- Added margin-top to give space between tables -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-center">Description</th>
                                                    <th class="text-center">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Total Sales -->
                                                <tr>
                                                    <td class="text-center"><strong>Total Sales</strong></td>
                                                    <td class="text-center">
                                                        {{ number_format($totalSales, 2) ?? '0.00' }}
                                                    </td>
                                                </tr>
                                                <!-- Total Refund -->
                                                <tr>
                                                    <td class="text-center"><strong>Total Refund</strong></td>
                                                    <td class="text-center">
                                                        {{ number_format($totalRefund, 2) ?? '0.00' }}
                                                    </td>
                                                </tr>
                                                <!-- Total Payment -->
                                                <tr>
                                                    <td class="text-center"><strong>Total Payment</strong></td>
                                                    <td class="text-center">
                                                        {{ number_format($totalPayment, 2) ?? '0.00' }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POS Details -->
                <div class="p-2 mt-4">
                    <div id="itemDetails" class="item-details mb-3">
                        <!-- Cart Table -->
                        <div class="mt-5">
                            <table class="cart-table shadow-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>{{__('Price')}}</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="itemDetailsBody">
                                    <!-- Dynamic cart rows will go here -->
                                    <!-- Example of empty cart -->
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No items in the cart</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer Section -->
                <div class="fixed-bottom-section bg-light shadow-lg p-4 rounded-top">
                    <!-- Total Section -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <strong class="text-dark fs-4">Total:</strong>
                        <span id="totalAmount" name="totalAmount" class="fs-5 fw-bold text-dark">0.00</span>
                    </div>

                    <!-- Discount Section -->
                    <div id="discountSection" class="d-flex justify-content-between mb-3" style="display:none;">
                        <strong class="text-dark fs-5">Discount:</strong>
                        <span id="discountAmount" class="discount fs-5 text-dark">-0.00</span>
                    </div>

                    <!-- Action Buttons (Pay and Hold) -->
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <!-- Hold Button -->
                        <button
                            class="btn btn-gradient-blue w-100 shadow-sm d-flex justify-content-center align-items-center fw-bold"
                            id="holdBtn" name="holdBtn" title="Hold Order">
                            Hold
                        </button>

                        <!-- Pay Button -->
                        <button class="btn btn-gradient-blue w-100 shadow-lg fw-bold" id="payBtn" name="payBtn"
                            data-bs-toggle="modal" data-bs-target="#paymentModal">Pay</button>
                    </div>
                </div>

                <!-- Modal for Hold -->
                <div class="modal fade" id="holdModal" tabindex="-1" aria-labelledby="holdModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="holdModalLabel">Hold Invoice</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Hold invoice?</strong> Same reference will replace the old list of existing!
                                </p>
                                <!-- Form for reference ID -->
                                <form id="holdForm">
                                    <div class="mb-3">
                                        <label for="refId" class="form-label">Reference ID</label>
                                        <input type="text" class="form-control" id="refId" name="refId"
                                            required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="holdConfirmBtn">Yes, OK</button>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

    </div>

    <!-- Payment Success Modal -->
    <div class="modal fade" id="paymentSuccessModal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg success-modal">
            <div class="modal-content text-center">
                <div class="modal-body">
                    <!-- Success Icon -->
                    <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                    <h4 class="mt-3 text-success">Payment Successful!</h4>
                    <p><strong>POS Number:</strong> <span id="PosNumber">-</span></p>
                    <p><strong>Total:</strong> <span id="totalPayment">0.00</span></p>
                    <p><strong>Amount Paid:</strong> <span id="amountPaid">0.00</span></p>
                    <p><strong>Change:</strong> <span id="changePayment">0.00</span></p>
                </div>
                <div class="modal-footer justify-content-center">
                    {{-- <button type="button" class="btn btn-outline-success btn-lg" data-bs-toggle="modal"
                        data-bs-target="#receiptModal">Receipt</button> --}}
                    <button type="button" class="btn btn-primary btn-lg" id="newTransactionBtn">New
                        Transaction</button>
                    <!-- Print Button -->
                    @if ($pointOfSale && isset($pointOfSale->id) && is_numeric($pointOfSale->id))
                        <button type="button" class="btn btn-info btn-lg" id="printReceiptBtn"
                            data-pos-id="{{ $pointOfSale->id }}">
                            Print
                        </button>
                    @else
                        <button type="button" class="btn btn-info btn-lg" id="printReceiptBtn"
                            data-pos-id="temp-id">
                            Print
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>




    <!-- Voucher Modal -->
    <div class="modal fade" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="voucherModalLabel">Enter Voucher Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="voucherCode">Voucher Code:</label>
                    <input type="text" class="form-control" id="voucherCode" placeholder="Enter voucher code">
                    <div id="voucherMessage" class="mt-2 text-danger" style="display:none;">Invalid voucher code!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="applyVoucherBtn">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Notes Modal -->
    <div class="modal fade" id="orderNotesModal" tabindex="-1" aria-labelledby="orderNotesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderNotesModalLabel">Order Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="orderNotes" rows="5"
                        placeholder="Add any additional notes for the order..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveNotesBtn">Save Notes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-lg shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="paymentModalLabel">Pilih Metode Pembayaran</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="paymentMethodsList" class="list-group">

                        <!-- Add more payment methods as needed -->
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Input Tunai -->
    <div class="modal fade" id="cashInputModal" tabindex="-1" aria-labelledby="cashInputModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cashInputModalLabel">Masukkan Nominal Uang Tunai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cashAmount" class="form-label">Nominal Uang Tunai:</label>
                        <input type="number" class="form-control" id="cashAmount"
                            placeholder="Masukkan nominal tunai">
                    </div>
                    <button class="btn btn-primary" id="confirmCashBtn">Konfirmasi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Transfer Bank -->
    <div class="modal fade" id="bankTransferModal" tabindex="-1" aria-labelledby="bankTransferModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bankTransferModalLabel">Transfer Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="selectedBankInfo"></p>
                    <img id="qrCodeImage" alt="QR Code" style="width: 100%; max-width: 300px;">
                </div>
            </div>
        </div>
    </div>
    <!-- Hidden Full Name Field -->
    <input type="hidden" id="fullname" value="{{ Auth::user()->fullname }}" />
    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <h4 class="mb-1">TDSmart</h4>
                        <p class="compact-text"><strong>POS Number:</strong> <span id="receiptPosNumber">-</span></p>
                        <p class="compact-text"><strong>Date:</strong> <span id="receiptDate"></span></p>
                        <p class="compact-text"><strong>Reserved by:</strong> <span id="receiptUser"></span></p>
                    </div>
                    <hr>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>{{__('Price')}}</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="receiptItems">
                            <!-- Dynamically generated rows for purchased items -->
                        </tbody>
                    </table>
                    <hr>
                    <div>
                        <strong>Notes:</strong>
                        <p id="receiptNotes" class="mt-2">No additional notes.</p>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <span id="receiptTotal">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Discount:</strong>
                        <span id="receiptDiscount">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Cash Paid:</strong>
                        <span id="receiptCashPaid">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Change:</strong>
                        <span id="receiptChange">0.00</span>
                    </div>
                    <hr>
                    <div class="text-center mt-4">
                        <p>Thank you for shopping!</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" id="closeReceiptModalBtn"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let totalAmount = 0;
        let discountAmount = 0;
        let paidCash = 0;
        let change = 0;
        let orderNotes = "";
        const addedItems = [];
        const allItems = @json($items);
        const paymentMethods = @json($paymentMethods);
        let selectedPaymentMethod = null;
        let posNumber = "";
        const departmentCode = 'DP01';


        // Function to check if the cart is empty before allowing the modal to show
        document.getElementById('holdBtn').addEventListener('click', function(event) {
            const cartItems = document.getElementById('itemDetailsBody').getElementsByTagName('tr');
            let cartIsEmpty = true;

            // Check if there are any items in the cart
            for (let i = 0; i < cartItems.length; i++) {
                if (cartItems[i].querySelector('td').innerText !== "No items in the cart") {
                    cartIsEmpty = false;
                    break;
                }
            }

            if (cartIsEmpty) {
                // Prevent the modal from opening if the cart is empty
                event.preventDefault();
                alert('No items in the cart. Please add items before holding the order.');
            } else {
                // Trigger the modal if the cart has items
                $('#holdModal').modal('show');
            }
        });

        document.getElementById('holdConfirmBtn').addEventListener('click', function() {
    const refId = document.getElementById('refId').value.trim();
    const cartItems = [];

    // Retrieve data from the cart rendered in the table
    const cartRows = document.querySelectorAll('#itemDetailsBody tr');
    cartRows.forEach(row => {
        const itemElement = row.querySelector('td:nth-child(1)');
        const itemPriceElement = row.querySelector('td:nth-child(2)');
        const itemQuantityElement = row.querySelector('td:nth-child(3)');
        const itemTotalElement = row.querySelector('td:nth-child(4)');

        if (itemElement && itemPriceElement && itemQuantityElement && itemTotalElement) {
            // Ensure no empty values are added to cartItems
            const item = {
                item: itemElement.innerText.trim(),
                price: parseFloat(itemPriceElement.innerText.replace(/[^\d.-]/g, '')) || 0,
                quantity: parseInt(itemQuantityElement.querySelector('span').innerText),
                total: parseFloat(itemTotalElement.innerText.replace(/[^\d.-]/g, '')) || 0,
            };
            if (item.item && item.total > 0) {  // Check for non-empty and valid item data
                cartItems.push(item);
            }
        }
    });

    // Validate Reference ID and cart items
    if (!refId) {
        alert('Please enter a Reference ID!');
        return;
    }
    if (cartItems.length === 0) {
        alert('Cart is empty. Add items to hold.');
        return;
    }

    // Ensure no items have a missing item name
    if (cartItems.some(item => !item.item || item.item.trim() === '')) {
        alert('One or more items have a missing name.');
        return;
    }

    console.log('Items to be sent:', JSON.stringify(cartItems));

    // Calculate the total amount
    const totalAmount = cartItems.reduce((sum, item) => sum + item.total, 0);
    console.log('Total Amount:', totalAmount);

    // Send data to the server using Fetch API
    fetch("{{ route('holdOrders.store') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            reference_id: refId,
            items: cartItems, // Pass cart items to backend
            total_amount: totalAmount, // Include the total amount
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Order successfully held!');
            document.getElementById('holdForm').reset();
            $('#holdModal').modal('hide');

            // Refresh the page without query string
            window.location.href = window.location.href.split('?')[0];
        } else {
            alert(`Failed to hold order: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});




        // Pass Laravel data to JavaScript
        const holdOrders = @json($holdOrders);

        function populateHoldOrders(page = 1, rowsPerPage = 10) {
            const tableBody = document.getElementById('holdOrdersList');
            tableBody.innerHTML = ''; // Clear existing rows

            // Sort holdOrders by id (ascending)
            const sortedOrders = holdOrders.sort((a, b) => a.id - b.id);

            // Pagination logic
            const startIndex = (page - 1) * rowsPerPage;
            const paginatedOrders = sortedOrders.slice(startIndex, startIndex + rowsPerPage);

            paginatedOrders.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
            <td>${order.id}</td>
            <td>${new Date(order.created_at).toLocaleDateString()}</td> <!-- Format date -->
            <td>${order.reference_id}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="editOrder(${order.id})" title="Edit">
                    <i class="fas fa-edit"></i> <!-- Icon for edit -->
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteOrder(${order.id})" title="Delete">
                    <i class="fas fa-trash"></i> <!-- Icon for delete -->
                </button>
            </td>
        `;
                tableBody.appendChild(row);
            });


            const paginationContainer = document.getElementById('pagination');
            paginationContainer.innerHTML = '';

            const totalPages = Math.ceil(holdOrders.length / rowsPerPage);

            for (let i = 1; i <= totalPages; i++) {
                const paginationButton = document.createElement('button');
                paginationButton.className = `btn btn-sm ${i === page ? 'btn-primary' : 'btn-light'} mx-1`;
                paginationButton.textContent = i;
                paginationButton.onclick = () => {
                    populateHoldOrders(i, rowsPerPage);
                    highlightActivePagination(i);
                };
                paginationContainer.appendChild(paginationButton);
            }

            // Automatically highlight the active page
            highlightActivePagination(page);
        }

        function highlightActivePagination(activePage) {
            const paginationButtons = document.querySelectorAll('#pagination button');
            paginationButtons.forEach((button, index) => {
                if (index + 1 === activePage) {
                    button.classList.remove('btn-light');
                    button.classList.add('btn-primary');
                } else {
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-light');
                }
            });
        }

        function editOrder(orderId) {
            const url = `/TDS/pos/hold-orders/${orderId}`; // URL to fetch the hold order details

            console.log(`Fetching details for hold order ID: ${orderId}`); // Log hold order ID

            // Fetch the hold order details from the server
            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Failed to fetch hold order details. HTTP status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Parsed hold order data (formatted):', JSON.stringify(data, null, 2));

                    if (data.success) {
                        const order = data.data; // Hold order data returned from the server

                        // Validate and populate the form with the hold order details
                        const refIdInput = document.getElementById('refId');

                        if (refIdInput) {
                            refIdInput.value = order.reference_id || ''; // Populate Reference ID
                        } else {
                            console.error("Element with id 'refId' not found.");
                        }

                        console.log('Form populated with hold order details.');

                        // Ensure hold cart_items (hold_order_details) is available
                        let holdCartItems = order.cart_items || order.details || [];

                        // Ensure holdCartItems is an array
                        if (!Array.isArray(holdCartItems)) {
                            console.log('Hold cart items is not an array, converting to array:');
                            holdCartItems = [holdCartItems]; // Wrap in an array
                        }

                        console.log('Hold cart items (formatted as table):');
                        console.table(holdCartItems);

                        // Update the cart table
                        const cartTableBody = document.getElementById('itemDetailsBody');
                        const totalAmountElement = document.getElementById('totalAmount');
                        const discountElement = document.getElementById('discountAmount');
                        let totalAmount = 0;
                        let discountAmount = order.discount || 0;

                        if (cartTableBody) {
                            cartTableBody.innerHTML = ''; // Clear existing items in cart

                            holdCartItems.forEach((item, index) => {
                                const name = item.item || 'N/A';
                                const price = item.price || 0;
                                const quantity = item.quantity || 1;
                                const total = price * quantity;

                                totalAmount += total;

                                const row = document.createElement('tr');
                                row.innerHTML = `
                            <td>${name}</td>
                            <td>${formatCurrency(price)}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="updateHoldQuantity(${index}, 'decrease', ${price})">-</button>
                                <span id="hold-quantity-${index}">${quantity}</span>
                                <button class="btn btn-sm btn-primary" onclick="updateHoldQuantity(${index}, 'increase', ${price})">+</button>
                            </td>
                            <td><span id="hold-total-${index}">${formatCurrency(total)}</span></td>
                        `;
                                cartTableBody.appendChild(row);
                            });

                            // Update total and discount elements
                            if (totalAmountElement) {
                                totalAmountElement.textContent = formatCurrency(totalAmount);
                            }

                            if (discountElement) {
                                discountElement.textContent = formatCurrency(discountAmount);
                            }

                            console.log('All hold cart items populated successfully.');
                        } else {
                            console.error("Element with id 'itemDetailsBody' not found.");
                        }
                    } else {
                        console.error('Failed to fetch hold order details:', data.message);
                        alert('Failed to load hold order details.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching hold order details:', error);
                    alert('An error occurred while fetching the hold order details.');
                });
        }

        function updateHoldQuantity(index, action, price) {
            // Get the item row and its quantity
            const quantityElement = document.getElementById(`hold-quantity-${index}`);
            let quantity = parseInt(quantityElement.textContent) || 1;

            // Adjust the quantity based on the action
            if (action === 'increase') {
                quantity++;
            } else if (action === 'decrease' && quantity > 1) {
                quantity--;
            }

            // Update the displayed quantity
            quantityElement.textContent = quantity;

            // Update the total for the item
            const totalElement = document.getElementById(`hold-total-${index}`);
            const total = price * quantity;
            totalElement.textContent = formatCurrency(total);

            // Recalculate the total amount for the cart
            updateOrderTotal(); // Recalculate the overall total after quantity change
        }
        // Helper function to format currency in Rupiah (IDR)
        function formatCurrency(value) {
            return `Rp ${value.toLocaleString('id-ID')}`; // Format as IDR currency
        }

        function updateOrderTotal() {
            let totalAmount = 0; // Reset total amount
            const cartRows = document.querySelectorAll('#itemDetailsBody tr'); // Select all rows in the cart table

            cartRows.forEach((row) => {
                const totalCell = row.querySelector('td:nth-child(4) span'); // Get the total cell (4th column)

                if (totalCell) {
                    // Parse the value by removing 'Rp', dots, and commas before converting to a number
                    const totalValue = parseFloat(
                        totalCell.textContent.replace(/Rp\s?/g, '').replace(/\./g, '').replace(',', '.').trim()
                    ) || 0;

                    totalAmount += totalValue; // Accumulate the total for all items
                }
            });

            // Update the total amount display element
            const totalAmountElement = document.getElementById('totalAmount');
            if (totalAmountElement) {
                totalAmountElement.textContent = formatCurrency(totalAmount); // Format and update the total display
            }

            console.log(`Overall total amount updated: ${totalAmount}`);
        }

        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order?')) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // CSRF token

                fetch(`/TDS/pos/hold-orders/${orderId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken, // Send CSRF token in headers
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // If successful, alert and redirect
                            alert('Order deleted successfully.');
                            window.location.href = '/TDS/pos'; // Adjust the URL based on your application's route
                        } else {
                            // If the server indicates failure
                            alert('Failed to delete order.');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting order:', error);
                        alert('An error occurred while deleting the order.');
                    });
            }
        }


        // Automatically populate the first page and trigger pagination highlight when the modal opens
        document.addEventListener('DOMContentLoaded', () => {
            populateHoldOrders();
            highlightActivePagination(1);
        });

        // Call this function when the modal is shown
        document.getElementById('holdOrdersModal').addEventListener('show.bs.modal', () => {
            populateHoldOrders();
        });


        // Declare a variable to store the selected customer code globally
        let selectedCustomerCode = null;

        // Handle customer search and selection
        document.getElementById('searchCustomer').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const customerList = document.getElementById('customerList');
            customerList.innerHTML = ''; // Clear previous search results

            // Get customer data from the server
            const customers = @json($customers);

            if (query === '') {
                customerList.style.display = 'none';
                return;
            }

            // Filter customers based on input
            const filteredCustomers = customers.filter(customer =>
                customer.customer_name.toLowerCase().includes(query) ||
                customer.customer_code.toString().includes(query)
            );

            // Display filtered results
            if (filteredCustomers.length > 0) {
                customerList.style.display = 'block'; // Show the customer list

                filteredCustomers.forEach((customer, index) => {
                    const customerItem = document.createElement('a');
                    customerItem.href = '#';
                    customerItem.className = 'list-group-item list-group-item-action';
                    customerItem.textContent =
                        `${customer.customer_name} (Kode: ${customer.customer_code})`;
                    customerItem.dataset.index = index;

                    // Add click event for selecting the customer
                    customerItem.addEventListener('click', function(event) {
                        event.preventDefault();
                        document.getElementById('searchCustomer').value = customer.customer_name;
                        customerList.innerHTML = ''; // Clear the customer list after selection
                        customerList.style.display = 'none'; // Hide the dropdown after selection

                        // Store selected customer code globally
                        selectedCustomerCode = customer.customer_code;
                        console.log('Customer selected:',
                            selectedCustomerCode); // Optional: log selected customer for debugging
                    });

                    customerList.appendChild(customerItem);
                });
            } else {
                customerList.style.display = 'none'; // Hide the dropdown if no customers match the query
            }
        });

        // Add navigation with arrow keys and enter key
        let selectedIndex = -1;

        document.getElementById('searchCustomer').addEventListener('keydown', function(event) {
            const customerList = document.getElementById('customerList');
            const items = customerList.querySelectorAll('.list-group-item');

            if (items.length === 0) return;

            if (event.key === 'ArrowDown') {
                // Navigate down
                if (selectedIndex < items.length - 1) {
                    selectedIndex++;
                }
            } else if (event.key === 'ArrowUp') {
                // Navigate up
                if (selectedIndex > 0) {
                    selectedIndex--;
                }
            } else if (event.key === 'Enter') {
                // Select the highlighted item
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    items[selectedIndex].click(); // Simulate click on the selected item
                }
                return; // Prevent form submission or other behavior
            }

            // Update highlight
            items.forEach((item, idx) => {
                if (idx === selectedIndex) {
                    item.classList.add('active'); // Highlight the active item
                    item.scrollIntoView({
                        block: 'nearest'
                    }); // Scroll to the highlighted item if needed
                } else {
                    item.classList.remove('active');
                }
            });

            // Update selectedCustomerCode when the arrow key selects an item
            if (selectedIndex >= 0 && selectedIndex < items.length) {
                const selectedCustomerItem = items[selectedIndex];
                const customerCode = filteredCustomers[selectedCustomerItem.dataset.index]?.customer_code;
                selectedCustomerCode = customerCode;
            }
        });

        document.getElementById('backBtn').addEventListener('click', () => {
            window.history.back();
        });

        // Fungsi untuk menyimpan catatan pesanan
        function saveOrderNotes() {
            const notes = document.getElementById('orderNotes').value.trim();

            // Simpan ke variabel global
            orderNotes = notes || "No additional notes.";

            // Tampilkan pesan konfirmasi (opsional)
            console.log("Order notes saved:", orderNotes);

            // Tutup modal setelah menyimpan
            const orderNotesModal = bootstrap.Modal.getInstance(document.getElementById('orderNotesModal'));
            orderNotesModal.hide();

            // Perbarui tampilan Receipt jika ada
            const receiptNotesElement = document.getElementById('receiptNotes');
            if (receiptNotesElement) {
                receiptNotesElement.textContent = orderNotes;
            }
        }

        // Event Listener untuk tombol Save Notes
        document.getElementById('saveNotesBtn').addEventListener('click', saveOrderNotes);


        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        }


        // Helper: Safely get an element
        function getElement(selector) {
            return document.querySelector(selector);
        }

        // Check if allItems is valid
        if (!allItems || !Array.isArray(allItems)) {
            console.error('Failed to load items from the backend.');
        } else {
            console.log('All items from backend:', allItems);

            const addedItems = [];
            let selectedIndex = -1; // For keyboard navigation

            // Function to display items as catalog cards
            function displayItemCatalog(items) {
                const itemCatalog = getElement('#itemCatalog');
                if (!itemCatalog) {
                    console.error('Element #itemCatalog not found.');
                    return; // Exit if itemCatalog is not found
                }

                itemCatalog.innerHTML = ''; // Clear existing items

                items.forEach(item => {
                    const salesPrice = item.item_sales_prices?.[0]?.sales_price || 0;
                    const priceDisplay = formatCurrency(salesPrice);

                    const itemCard = document.createElement('div');
                    itemCard.classList.add('card', 'shadow-sm', 'item-card');
                    itemCard.style.cursor = 'pointer'; // Pointer cursor on hover
                    itemCard.innerHTML = `
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h6 class="card-title">${item.item_name}</h6>
                    <p class="card-text">${priceDisplay}</p>
                </div>
            `;

                    itemCard.addEventListener('click', function() {
                        console.log(`Item clicked: ${item.item_name}`);
                        addItemToCart(item.item_code, item.item_name, salesPrice);
                    });

                    itemCatalog.appendChild(itemCard);
                });
            }

            // Call function to display catalog
            displayItemCatalog(allItems);
        }


        // Function to update the receipt modal
        function updateReceiptModal() {
            // Get the current date and user details
            const receiptDate = new Date().toLocaleDateString();
            const loggedInUser = @json(Auth::user()->fullname); // Nama pengguna login dari backend
            const receiptPosNumber = posNumber || "-"; // Assuming `posNumber` is set somewhere in your process
            const PosNumber = posNumber || "-";

            console.log("Receipt Modal Update Started");

            // Log the receipt header information
            console.log("Receipt Date:", receiptDate);
            console.log("Logged in User:", loggedInUser);
            console.log("POS Number:", receiptPosNumber);
            console.log("POS Number (alt):", PosNumber);

            // Update the receipt header
            document.getElementById("receiptDate").textContent = receiptDate;
            document.getElementById("receiptUser").textContent = loggedInUser;
            document.getElementById("receiptPosNumber").textContent = receiptPosNumber; // Display POS number
            document.getElementById("PosNumber").textContent = PosNumber;

            const receiptItems = document.getElementById("receiptItems");
            receiptItems.innerHTML = ''; // Clear previous items

            totalAmount = 0; // Reset total amount
            console.log("Initial Total Amount:", totalAmount);

            // Populate items in the receipt
            addedItems.forEach(item => {
                console.log(`Item Name: ${item.name}, Item Quantity: ${item.qty}, Item Price: ${item.price}`);

                const row = document.createElement("tr");
                row.innerHTML = `
            <td>${item.name}</td>
            <td>${item.qty}</td>
            <td>${formatCurrency(item.price)}</td>
            <td>${formatCurrency(item.price * item.qty)}</td>
        `;
                receiptItems.appendChild(row);

                totalAmount += item.price * item.qty; // Accumulate total price
                console.log("Updated Total Amount after item:", totalAmount);
            });

            // Calculate the final total and change
            const finalTotal = totalAmount - discountAmount;
            const change = paidCash - finalTotal; // Calculate change

            // Log final totals and changes
            console.log("Total Amount (before discount):", totalAmount);
            console.log("Discount Amount:", discountAmount);
            console.log("Final Total (after discount):", finalTotal);
            console.log("Cash Paid:", paidCash);
            console.log("Change (Cash Paid - Final Total):", change);

            // Update the receipt modal fields
            document.getElementById("receiptTotal").textContent = formatCurrency(finalTotal);
            document.getElementById("receiptDiscount").textContent = formatCurrency(discountAmount || 0);
            document.getElementById("receiptCashPaid").textContent = formatCurrency(paidCash || 0); // Use paidCash here
            document.getElementById("receiptChange").textContent = formatCurrency(change || 0);

            // Add event listener to the close button to refresh the page when clicked
            const closeButton = document.getElementById('closeReceiptModalBtn'); // Assuming the button has this ID
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    console.log("Closing receipt modal and refreshing the page.");
                    location.reload(); // Refresh the page
                });
            }

            console.log("Receipt Modal Updated Successfully.");
        }



        // Update Total Amount in Cart
        function updateTotalAmount() {
            totalAmount = addedItems.reduce((sum, item) => sum + item.price * item.qty, 0);
            getElement('#totalAmount').textContent = formatCurrency(totalAmount - discountAmount);
        }


        // Debounce function untuk mengatur delay input
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        const socket = new WebSocket('ws://localhost:8000');
        // {{__('Add Item')}} to Cart
        function addItemToCart(itemId, itemName, itemPrice) {
            const existingItem = addedItems.find(item => item.id === itemId);

            if (existingItem) {
                existingItem.qty += 1;
            } else {
                addedItems.push({
                    id: itemId,
                    name: itemName,
                    price: itemPrice,
                    qty: 1
                });
            }

            renderCart();
            updateReceiptModal();

            socket.send(JSON.stringify({ type: 'updateCart', cart: addedItems }));
        }

        // Render the cart
        function renderCart() {
            const cartBody = getElement("#itemDetailsBody");
            cartBody.innerHTML = '';

            addedItems.forEach(item => {
                const row = document.createElement("tr");
                row.innerHTML = `
            <td>${item.name}</td>
            <td>${formatCurrency(item.price)}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="updateQuantity('${item.id}', -1)">-</button>
                <span id="qty-${item.id}">${item.qty}</span>
                <button class="btn btn-sm btn-primary" onclick="updateQuantity('${item.id}', 1)">+</button>
            </td>
            <td>${formatCurrency(item.price * item.qty)}</td>
        `;
                cartBody.appendChild(row);
            });

            updateTotalAmount(); // Update total amount in the cart
        }

        // Update quantity in cart
        function updateQuantity(itemId, change) {
            const item = addedItems.find(item => item.id === itemId);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) {
                    addedItems.splice(addedItems.indexOf(item), 1); // Remove item if quantity is 0
                }
                renderCart();
                updateReceiptModal();
            }
        }

        // Render Item List from Search (this should not reset cart items)
        function renderItemList(filteredItems) {
            const itemList = getElement('#itemList');
            itemList.innerHTML = ''; // Clear the item list

            // If the search bar is empty, don't render anything
            const searchBar = getElement('#searchItem');
            if (searchBar.value.trim() === '') {
                return;
            }

            if (filteredItems.length === 0) {
                itemList.innerHTML = '<p class="no-items">No Items Found</p>';
                return;
            }

            filteredItems.forEach((item) => {
                const price = item.item_sales_prices.length > 0 ? item.item_sales_prices[0].sales_price : 0;
                const listItem = document.createElement('button');
                listItem.classList.add('list-group-item', 'list-group-item-action');
                listItem.dataset.id = item.item_code;
                listItem.dataset.name = item.item_name;
                listItem.dataset.price = price;

                listItem.textContent = `${item.item_name} - ${formatCurrency(price)}`;

                // When an item is clicked, add it to the cart
                listItem.addEventListener('click', () => {
                    addItemToCart(item.item_code, item.item_name, price);

                    // Clear the search bar and the item list after selecting an item
                    searchBar.value = '';
                    renderItemList([]); // Optionally, reset item list after adding
                });

                itemList.appendChild(listItem);
            });

            // Reset selectedIndex every time the list is rendered
            selectedIndex = -1;
            updateHighlight();
        }

        // Filter Items based on search text
        function filterItems(searchText) {
            const filteredItems = allItems.filter(item => {
                const searchLower = searchText.toLowerCase();
                const matchesName = item.item_name.toLowerCase().includes(searchLower);
                const matchesBarcode = item.item_sales_prices.some(price =>
                    price.barcode && price.barcode.toLowerCase().includes(searchLower)
                );
                return matchesName || matchesBarcode;
            });

            renderItemList(filteredItems);
        }

        // Event listener for search input
        getElement('#searchItem').addEventListener('input', (event) => {
            const searchText = event.target.value.trim(); // Get search text
            filterItems(searchText); // Call filterItems to search and render items
        });

        // Update Quantity in Cart
        function updateQuantity(itemId, change) {
            const item = addedItems.find(item => item.id === itemId);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) {
                    addedItems.splice(addedItems.indexOf(item), 1);
                }
                renderCart(); // Re-render the cart
                updateReceiptModal(); // Update receipt modal
            }
        }

        // Update Highlight (optional for visual enhancement)
        function updateHighlight() {
            const itemList = getElement('#itemList');
            const buttons = itemList.querySelectorAll('.list-group-item');

            buttons.forEach((btn, idx) => {
                if (idx === selectedIndex) {
                    btn.classList.add('highlight'); // Add highlight class to the selected item
                } else {
                    btn.classList.remove('highlight'); // Remove highlight from other items
                }
            });
        }


        // Apply Discount (Voucher)
        function setDiscount(amount) {
            discountAmount = parseFloat(amount || 0);
            const discountSection = getElement('#discountSection');
            if (discountAmount > 0) {
                getElement('#discountAmount').textContent = `-${formatCurrency(discountAmount)}`;
                discountSection.style.display = 'flex';
            } else {
                discountSection.style.display = 'none';
            }
            updateTotalAmount();
            console.log('Discount applied:', discountAmount);
        }

        // Apply Voucher Logic
        getElement('#applyVoucherBtn').addEventListener('click', () => {
            const voucherCode = getElement('#voucherCode').value.trim();
            if (voucherCode === "DISCOUNT10") {
                setDiscount(10);
                $('#voucherModal').modal('hide');
                getElement('#voucherCode').value = '';
            } else {
                getElement('#voucherMessage').style.display = 'block';
            }
            console.log('Voucher code applied:', voucherCode);
        });



        function determinePaymentType(paymentName) {
            const lowerCaseName = paymentName.toLowerCase();

            if (lowerCaseName.includes('tunai') || lowerCaseName.includes('cash')) {
                return 'Cash';
            } else if (
                lowerCaseName.includes('bank') ||
                lowerCaseName.includes('transfer') ||
                lowerCaseName.includes('bca') ||
                lowerCaseName.includes('mandiri')
            ) {
                return 'Bank Transfer';
            } else {
                return 'Other';
            }
        }

        function selectPayment(paymentMethodCode) {
            const selectedMethod = paymentMethods.find(
                method => method.payment_method_code === paymentMethodCode
            );

            if (!selectedMethod) {
                alert('Metode pembayaran tidak ditemukan!');
                return;
            }

            const paymentType = determinePaymentType(selectedMethod.payment_name);

            if (paymentType === 'Cash') {
                $('#paymentModal').modal('hide');
                $('#cashInputModal').modal('show');
            } else if (paymentType === 'Bank Transfer') {
                $('#paymentModal').modal('hide');
                $('#bankTransferModal').modal('show');
                document.getElementById('selectedBankInfo').textContent =
                    `Bank Tujuan: ${selectedMethod.payment_name}`;
                document.getElementById('qrCodeImage').src = selectedMethod.qr_code;
            } else {
                alert('Jenis pembayaran tidak didukung!');
            }

            // Generate a new POS number (assuming it's unique for each transaction)
            posNumber = 'POS-' + new Date()
                .toLocaleString(); // Mock generation of POS number based on the current time
            selectedPaymentMethod = selectedMethod
            console.log('Metode pembayaran dipilih:', selectedMethod);
            console.log('POS Number:', posNumber); // Add POS number to the console log
        }

        // Handle cash confirmation and transaction
        document.getElementById('confirmCashBtn').addEventListener('click', () => {
            const cashAmount = parseFloat(document.getElementById('cashAmount').value);

            // Validate if the cash amount is a valid positive number
            if (isNaN(cashAmount) || cashAmount <= 0) {
                alert('Masukkan nominal uang tunai yang valid!');
                return;
            }

            const finalTotal = totalAmount - discountAmount;

            // Check if the cash amount is enough to cover the total payment
            if (cashAmount >= finalTotal) {
                // Calculate change
                const paidCash = cashAmount;
                const change = cashAmount - finalTotal;

                // Update payment success modal fields
                document.getElementById('amountPaid').innerText = formatCurrency(cashAmount);
                document.getElementById('changePayment').innerText = formatCurrency(change);
                document.getElementById('totalPayment').innerText = formatCurrency(finalTotal);

                // Close cash input modal
                $('#cashInputModal').modal('hide');

                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Ensure selectedPaymentMethod is defined and has payment_method_code
                const paymentMethodCode = selectedPaymentMethod?.payment_method_code || null;

                // Validate payment method code before proceeding
                if (!paymentMethodCode) {
                    alert('Metode pembayaran belum dipilih!');
                    return;
                }

                // Validate departmentCode
                if (!departmentCode) {
                    alert('Kode departemen tidak valid!');
                    return;
                }

                // Check if customer is selected
                if (!selectedCustomerCode) {
                    alert('Pelanggan belum dipilih!');
                    return;
                }

                const requestData = {
                    items: addedItems.map(item => ({
                        id: item.id,
                        name: item.name,
                        qty: item.qty,
                        price: item.price,
                    })),
                    total_amount: totalAmount,
                    discount: discountAmount,
                    final_amount: finalTotal,
                    payment_method: paymentMethodCode,
                    cash_received: paidCash,
                    change: change,
                    user_id: @json(Auth::id()),
                    notes: orderNotes || '',
                    fullname: document.getElementById('fullname') ? document.getElementById('fullname').value :
                        '',
                    pos_number: posNumber,
                    department_code: departmentCode,
                    customer_id: selectedCustomerCode
                };

                // Debugging: Log the data before sending
                console.log('Data to send:', requestData);

                // AJAX request with CSRF token included in headers
                $.ajax({
                    url: '{{ route('pos.saveTransaction') }}',
                    method: 'POST',
                    data: requestData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        const posNumber = response.pos_number || '-';
                        const posId = response.posId;

                        // Save posId to currentPosId
                        handlePaymentSuccess(posId);

                        console.log('Transaksi berhasil');
                        console.log('POS Number:', posNumber);
                        console.log('Total Payment:', formatCurrency(finalTotal));
                        console.log('Cash Paid:', formatCurrency(cashAmount));
                        console.log('Change:', formatCurrency(change));

                        const paymentSuccessModal = new bootstrap.Modal(document.getElementById(
                            'paymentSuccessModal'));
                        paymentSuccessModal.show();

                        // Reset transaction for a new entry
                        resetTransaction();
                    },
                    error: function(xhr, status, error) {
                        console.error("Terjadi kesalahan saat menyimpan transaksi:", error);
                        console.error('Response:', xhr.responseText);
                        alert("Terjadi kesalahan saat menyimpan transaksi.");
                    }
                });
            } else {
                alert('Uang tunai tidak cukup untuk pembayaran!');
            }
        });


        document.getElementById('newTransactionBtn').addEventListener('click', () => {

            location.reload();
        });


        function renderPaymentMethods() {
            const paymentList = getElement('#paymentMethodsList');
            paymentList.innerHTML = ''; // Bersihkan daftar sebelumnya

            paymentMethods.forEach(method => {
                const paymentType = determinePaymentType(method.payment_name); // Tentukan tipe pembayaran
                const listItem = document.createElement('button');
                listItem.classList.add('list-group-item', 'list-group-item-action');
                listItem.textContent = `${method.payment_name}`; // Tampilkan nama pembayaran
                listItem.addEventListener('click', () =>
                    selectPayment(method.payment_method_code)
                ); // Gunakan payment_method_code untuk seleksi
                paymentList.appendChild(listItem);
            });
        }

        // Render metode pembayaran saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            renderPaymentMethods();

            const orderNotesModal = document.getElementById('orderNotesModal');
            orderNotesModal.addEventListener('show.bs.modal', () => {
                document.getElementById('orderNotes').value = orderNotes;
            });
        });


        function resetTransaction() {

            let addedItems = [];
            let totalAmount = 0;
            let discountAmount = 0;
            let paidCash = 0;
            let change = 0;
            let selectedPaymentMethod = null;

            // Clear UI and prepare for a new transaction
            document.getElementById('receiptItems').innerHTML = '';
            document.getElementById('cashAmount').value = '';
            document.getElementById('selectedBankInfo').textContent = '';
            document.getElementById('qrCodeImage').src = '';
            updateReceiptModal();
        }

        let currentPosId = null; // Variabel untuk menyimpan posId saat ini
        let lastPosId = 0; // Variabel untuk melacak posId terakhir

        // Fungsi untuk menangani pembayaran yang berhasil
        function handlePaymentSuccess(posId) {
            // Validasi apakah posId valid
            if (posId && !isNaN(posId) && posId > lastPosId) {
                currentPosId = posId; // Perbarui currentPosId dengan posId terbaru
                lastPosId = posId; // Perbarui lastPosId dengan posId terbaru

            } else {
                console.error('Kesalahan: posId tidak valid atau transaksi belum dilakukan.');
            }
        }

        // Fungsi untuk memproses pembayaran dan mendapatkan posId terbaru
        function processPayment() {
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    // Simulasikan keberhasilan transaksi
                    const newPosId = lastPosId + 1; // Tambahkan 1 ke lastPosId
                    resolve({
                        posId: newPosId // Kirim posId terbaru
                    });
                }, 1000);
            });
        }

        // Fungsi untuk memulai transaksi
        function startTransaction() {
            processPayment()
                .then(response => {
                    // Panggil handlePaymentSuccess hanya setelah transaksi berhasil
                    handlePaymentSuccess(response.posId);
                })
                .catch(error => {
                    console.error(`Terjadi kesalahan: ${error.message}`);
                });
        }

        // Memulai transaksi hanya saat dipanggil
        startTransaction(); // Hanya memulai jika dipanggil

        // Event listener untuk tombol "Print Receipt"
        document.getElementById('printReceiptBtn').addEventListener('click', function() {
            // Periksa apakah currentPosId ada
            if (currentPosId) {
                const receiptUrl = `/TDS/pos/print-receipt/${currentPosId}`;

                // Buka jendela baru untuk halaman cetak
                const receiptWindow = window.open(receiptUrl, '_blank', 'width=800,height=600');
                console.log('Attempting to open the receipt window.');

                // Periksa jika jendela tidak dapat dibuka (popup blocker)
                if (!receiptWindow) {
                    console.error('Failed to open the receipt window. Check popup blocker settings.');
                    alert('Gagal membuka jendela cetak. Periksa pengaturan popup browser Anda.');
                    return;
                }

                console.log('Receipt window opened successfully:', receiptWindow);

                // Tunggu hingga halaman resi sepenuhnya dimuat
                receiptWindow.onload = function() {
                    console.log('Receipt window loaded. Triggering print dialog.');
                    receiptWindow.print(); // Memicu dialog print

                    // Setelah mencetak, tutup jendela
                    receiptWindow.onafterprint = function() {
                        console.log('Printing completed. Closing receipt window.');
                        receiptWindow.close(); // Menutup jendela setelah pencetakan selesai
                    };
                };

                // Tangani jika ada kesalahan saat memuat jendela
                receiptWindow.onerror = function() {
                    console.error('An error occurred while loading the receipt window.');
                    alert('Terjadi kesalahan saat membuka jendela cetak. Silakan coba lagi.');
                };
            } else {
                // Jika currentPosId tidak ada, tampilkan pesan error
                alert('Tidak ada posId yang valid untuk dicetak.');
            }
        });

        document.getElementById('fullscreenBtn').addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                document.getElementById('fullscreenBtn').innerHTML =
                    '<i class="fas fa-compress"></i>'; // Change icon to "Exit Fullscreen"
                document.getElementById('fullscreenBtn').title = 'Exit Fullscreen';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    document.getElementById('fullscreenBtn').innerHTML =
                        '<i class="fas fa-expand"></i>'; // Change icon back to "Fullscreen"
                    document.getElementById('fullscreenBtn').title = 'Fullscreen';
                }
            }
        });

        document.getElementById('closeRegisterBtn').addEventListener('click', function(event) {
            event.preventDefault();
            const closeRegisterModal = new bootstrap.Modal(document.getElementById('closeRegisterModal'));
            closeRegisterModal.show();
        });

        document.getElementById('registerDetailBtn').addEventListener('click', function(event) {
            event.preventDefault();
            const registerDetailModal = new bootstrap.Modal(document.getElementById('registerDetailModal'));
            registerDetailModal.show();
        });
    </script>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>


</html>
