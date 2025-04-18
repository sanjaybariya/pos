<div class="row ">

    <div class="col-md-8 no-print">
        <div class="col-md-12">
            <h4 class="text-right">Store Location:: {{ $this->branch_name }}</h4>

        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <form wire:submit.prevent="searchTerm" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Scen Barcode /Enter Product Name "
                                wire:model.lazy="searchTerm">
                        </div>
                    </form>
                    @if ($showSuggestions && count($searchResults) > 0)
                        <div class="search-results">

                            <div class="list-group mb-3 ">
                                @foreach ($searchResults as $product)
                                    <a href="#" class="list-group-item list-group-item-action"
                                        wire:click.prevent="addToCart({{ $product->id }})">
                                        <strong>{{ $product->name }} ({{ $product->size }})</strong><br>
                                        <small>₹{{ number_format(@$product->inventorie->sell_price, 2) }}</small>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <div class="col-md-4">
                @if (auth()->user()->hasRole('cashier'))

                    <div class="form-group">
                        <select id="commissionUser" class="form-control" wire:model="selectedCommissionUser"
                            wire:change="calculateCommission">
                            <option value="">-- Select Commission Customer --</option>
                            @foreach ($commissionUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                @endif
                @if (auth()->user()->hasRole('warehouse'))
                    <div class="form-group">
                        <select id="partyUser" class="form-control" wire:model="selectedPartyUser"
                            wire:change="calculateParty">
                            <option value="">-- Select a Party Customer --</option>
                            @foreach ($partyUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}
                                    ({{ $user->credit_points }}pt)
                                </option>
                            @endforeach
                        </select>

                    </div>
                @endif

            </div>
            @if ($selectedPartyUser || $selectedCommissionUser)
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" id="customer" class="btn btn-primary mt-2" data-toggle="modal"
                                data-target="#captureModal">
                                Take picture
                            </button>
                        </div>

                    </div>
                </div>
            @endif
        </div>

        <div class="table-responsive" style="max-height: 700px; min-height: 520px; overflow-y: auto;">
            <table class="table table-bordered" id="cartTable">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 50%;">Product</th>
                        <th style="width: 20%;">Quantity</th>
                        <th style="width: 10%;">Price</th>
                        <th style="width: 10%;">Total</th>
                        <th style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemCarts as $item)
                        <tr>
                            <td class="product-name" style="word-wrap: break-word; width: 50%;">
                                <strong>{{ $item->product->name }}</strong><br>
                                <small>{{ $item->product->description }}</small>
                            </td>
                            <td style="width: 20%;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <button class="btn btn-sm btn-outline-success"
                                        wire:click="decrementQty({{ $item->id }})">−</button>
                                    <input type="number" min="1"
                                        class="form-control form-control-sm mx-2 text-center"
                                        wire:model.lazy="quantities.{{ $item->id }}"
                                        wire:change="updateQty({{ $item->id }})" />
                                    <button class="btn btn-sm btn-outline-warning"
                                        wire:click="incrementQty({{ $item->id }})">+</button>
                                </div>
                            </td>
                            <td style="width: 10%;">
                                @if (@$item->product->inventorie->discount_price && $this->commissionAmount > 0)
                                    <span class="text-danger">
                                        ₹{{ number_format(@$item->product->inventorie->sell_price, 2) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <s>₹{{ number_format(@$item->product->inventorie->discount_price, 2) }}</s>
                                    </small>
                                @else
                                    ₹{{ number_format(@$item->product->inventorie->sell_price, 2) }}
                                @endif
                            </td>
                            <td style="width: 10%;">
                                ₹{{ number_format(@$item->product->inventorie->sell_price * $item->quantity, 2) }}
                            </td>
                            <td style="width: 10%;">
                                <button class="btn btn-sm btn-danger"
                                    wire:click="removeItem({{ $item->id }})">Remove</button>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No products found in the cart.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                {{-- {{ $itemCarts->links('components.pagination.custom') }} --}}
            </div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-body p-0">
                <table class="table table-bordered text-center mb-0">
                    <thead class="">
                        <tr>
                            <th>Quantity</th>
                            <th>MRP</th>
                            <th>Rounded Off</th>
                            <th>Total Payable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{ number_format($this->cartCount, 0) }}
                                <input type="hidden" id="cartCount" value="{{ $this->cartCount }}">
                            </td>
                            <td>
                                ₹{{ number_format($this->total, 2) }}
                                <input type="hidden" id="mrp" value="{{ $this->total }}">
                            </td>
                            <td>
                                ₹{{ number_format(round($this->total), 2) }}
                                <input type="hidden" id="roundedTotal" value="{{ round($this->total) }}">
                            </td>
                            <td class="table-success fw-bold">
                                ₹{{ number_format($this->total, 2) }}
                                <input type="hidden" id="totalPayable" value="{{ $this->total }}">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button wire:click="toggleBox" class="btn btn-lg btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Cash
                                </button>

                            </td>
                            <td>
                                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Online
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Hold
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Cash + UPI
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


    </div>


    <!-- Single Modal -->
    <div class="modal fade no-print " id="captureModal" tabindex="-1" aria-labelledby="captureModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-sm rounded-4 border-0">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="captureModalLabel">
                        <i class="bi bi-camera-video me-2"></i>Image Capture
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body px-4 py-4">
                    <!-- Step 1: Product -->
                    <div id="step1">
                        <h6 class="text-muted mb-3">Step 1: Capture Product Image</h6>
                        <div class="border rounded-3 overflow-hidden mb-3">
                            <video id="video1" class="w-100" autoplay></video>
                            <canvas id="canvas1" class="d-none"></canvas>
                        </div>
                        <button type="button" class="btn btn-outline-primary w-100"
                            onclick="captureImage('product')">
                            <i class="bi bi-camera me-1"></i>Capture Product Image
                        </button>
                    </div>

                    <!-- Step 2: User -->
                    <div id="step2" class="d-none mt-4">
                        <h6 class="text-muted mb-3">Step 2: Capture User Image</h6>
                        <div class="border rounded-3 overflow-hidden mb-3">
                            <video id="video2" class="w-100" autoplay></video>
                            <canvas id="canvas2" class="d-none"></canvas>
                        </div>
                        <div class="d-flex justify-content-between gap-2">
                            <button type="button" class="btn btn-outline-primary w-100"
                                onclick="captureImage('user')">
                                <i class="bi bi-camera me-1"></i>Capture User Image
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100" data-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-4 no-print">

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-0">🛒 Cart Summary</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-outline-danger ms-2" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Logout"
                            onclick="document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>

                    </div>
                </div>

            </div>
            @include('layouts.flash-message')
            <div class="card-body">
                @if ($showBox)

                    <div id="cash-payment">

                        <form onsubmit="event.preventDefault(); calculateCash();" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="cash" class="form-label">Cash Amount</label>

                                    <input type="number" class="form-control" id="cash"
                                        value="{{ $this->total }}" placeholder="Enter Cash Amount"
                                        oninput="calculateChange()" readonly>

                                </div>

                                <div class="col-md-4">
                                    <label for="tender" class="form-label">Tendered Amount</label>
                                    <input type="number" wire:model="cashPaTenderyAmt" class="form-control"
                                        id="tender" placeholder="Enter Tendered Amount"
                                        oninput="calculateChange()">
                                </div>

                                <div class="col-md-4">
                                    <label for="change" class="form-label">Change</label>
                                    <input type="number" wire:model="cashPayChangeAmt" class="form-control"
                                        id="change" readonly>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- <h6 class="mb-3">💵 Enter Cash Denominations</h6> --}}
                            <div class="row g-3">
                                <div class="col-md-12">

                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Currency</th>
                                                <th>Nos</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($noteDenominations as $key => $denomination)
                                                <tr>
                                                    <td>₹{{ $denomination }}</td>
                                                    <td>
                                                        <input type="number"
                                                            wire:model="cashNotes.{{ $key }}.{{ $denomination }}"
                                                            class="form-control" id="notes_{{ $denomination }}"
                                                            value="0" min="0"
                                                            oninput="calculateCashBreakdown()">
                                                    </td>
                                                    <td id="sum_{{ $denomination }}">₹0</td>
                                                </tr>
                                            @endforeach
                                                <tr>
                                                    <td colspan="2" class="text-end fw-bold">Total Cash</td>
                                                    <td id="totalNoteCash">₹0</td>
                                                </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            <div class="border p-3 rounded bg-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Subtotal</strong>
                                    <span>₹{{ number_format($sub_total, 2) }}</span>
                                </div>

                                @if ($commissionAmount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Commission Deduction</strong>
                                        <span>- ₹{{ number_format($commissionAmount, 2) }}</span>
                                    </div>
                                @endif
                                @if ($partyAmount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Point Deduction</strong>
                                        <span>- ₹{{ number_format($partyAmount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between">
                                    <strong>Total Payable</strong>
                                    <span>₹{{ number_format($this->total, 2) }}</span>
                                    <input type="text" id="total" value="{{ $this->total }}"
                                        class="d-none" />
                                </div>
                            </div>
                            <p id="result" class="mt-3 fw-bold text-success"></p>
                            <div class="mt-4">
                                @if ($selectedCommissionUser || $selectedPartyUser)
                                    <button id="paymentSubmit" class="btn btn-primary btn-block mt-4"
                                        style="display:none" wire:click="checkout" wire:loading.attr="disabled">
                                        Submit
                                    </button>
                                @endif
                                <div wire:loading class="mt-2 text-muted">Processing payment...</div>
                            </div>

                        </form>
                    </div>
                @else
                    <div class="d-flex justify-content-between">
                        <strong>No Data Found</strong>
                    </div>
                @endif


            </div>
        </div>

    </div>
  
    @if ($invoiceData)
       
        <div class="col-lg-12 print-only">
            <div class="card card-block card-stretch card-height print rounded">
                <div class="card-header d-flex justify-content-between bg-primary header-invoice">
                    <div class="iq-header-title">
                        <h4 class="card-title mb-0">Invoice #{{ $invoiceData->invoice_number }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <img src="{{ asset('assets/images/logo.png') }}" class="logo-invoice img-fluid mb-3">
                            <h5 class="mb-0">Hello, {{ $invoiceData->customer_name }}</h5>
                            <p>Thank you for your business. Below is the summary of your invoice.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive-sm">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Order Date</th>
                                            <th scope="col">Order Status</th>
                                            <th scope="col">Order ID</th>
                                            {{-- <th scope="col">Billing Address</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $invoiceData->created_at->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $invoiceData->status == 'Paid' ? 'success' : 'danger' }}">
                                                    {{ $invoiceData->status }}
                                                </span>
                                            </td>
                                            <td>{{ $invoiceData->invoice_number }}</td>
                                            {{-- <td>
                                                <p class="mb-0">{{ $invoiceData->billing_address }}</p>
                                            </td> --}}
                                           
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Order Summary</h5>
                            <div class="table-responsive-sm">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" scope="col">#</th>
                                            <th scope="col">Item</th>
                                            <th class="text-center" scope="col">Quantity</th>
                                            <th class="text-center" scope="col">Price</th>
                                            <th class="text-center" scope="col">Totals</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoiceData->items as $i => $item)
                                        <tr>
                                            <th class="text-center" scope="row">{{ $i + 1 }}</th>
                                            <td>
                                                <h6 class="mb-0">{{ $item['name'] }}</h6>
                                            </td>
                                            <td class="text-center">{{ $item['quantity'] }}</td>
                                            <td class="text-center">₹{{ number_format($item['price'], 2) }}</td>
                                            <td class="text-center"><b>₹{{ number_format($item['price'] * $item['quantity'], 2) }}</b></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4 mb-3">
                        <div class="offset-lg-8 col-lg-4">
                            <div class="or-detail rounded">
                                <div class="p-3">
                                    <h5 class="mb-3">Order Details</h5>
                                    <div class="mb-2">
                                        <h6>Sub Total</h6>
                                        <p>₹{{ number_format($invoiceData->sub_total, 2) }}</p>
                                    </div>
                                    @if($invoiceData->commission_amount > 0)
                                    <div class="mb-2">
                                        <h6>Commission Deduction</h6>
                                        <p>- ₹{{ number_format($invoiceData->commission_amount, 2) }}</p>
                                    </div>
                                    @endif
                                    @if($invoiceData->party_amount > 0)
                                    <div class="mb-2">
                                        <h6>Party Deduction</h6>
                                        <p>- ₹{{ number_format($invoiceData->party_amount, 2) }}</p>
                                    </div>
                                    @endif
                                </div>
                                <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                    <h6>Total</h6>
                                    <h3 class="text-primary font-weight-700">₹{{ number_format($invoiceData->total, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <b class="text-danger">Notes:</b>
                            <p class="mb-0">Thank you for your business. If you have any questions, feel free to contact us.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>
<script>
    window.addEventListener('triggerPrint', () => {
        
        setTimeout(() => {
            window.print();
        }, 300);
    });

    window.onafterprint = () => {
        window.location.reload();
    };
</script>


<script>
    window.addEventListener('show-cash-modal', () => {
        let modal = new bootstrap.Modal(document.getElementById('cashModal'));
        modal.show();
    });
</script>

<script>
    window.addEventListener('user-selection-updated', event => {
        const userId = event.detail.userId;
        yourJsFunction(userId);
    });

    function yourJsFunction(userId) {

        console.log("JS function called with user ID:", userId);
        // Your custom logic here
    }

    function calculateChange() {
        let cash = parseFloat(document.getElementById("cash").value);
        let tender = parseFloat(document.getElementById("tender").value);

        if (isNaN(cash) || isNaN(tender)) {
            document.getElementById("change").value = '';
            document.getElementById("notes-breakdown").innerHTML = '';
            return;
        }

        let change = tender - cash;
        document.getElementById("change").value = change.toFixed(2);

        // Only show note breakdown if there's positive change
        let notes = [2000, 500, 100, 50, 20, 10, 5, 1];
        let breakdown = '';
        let remaining = change;

        if (change >= 1) {
            notes.forEach(function(note) {
                if (remaining >= note) {
                    let qty = Math.floor(remaining / note);
                    breakdown += `${note} x ${qty} note(s)<br>`;
                    remaining %= note;
                }
            });
        } else if (change < 0) {
            breakdown = `Remaining amount to collect: ₹${Math.abs(change).toFixed(2)}`;
        } else {
            breakdown = `Exact amount received. No change needed.`;
        }

        //  document.getElementById("notes-breakdown").innerHTML = breakdown;
    }

    function calculateCash() {
        const notes2000 = parseInt(document.getElementById('notes_2000').value) || 0;
        const notes500 = parseInt(document.getElementById('notes_500').value) || 0;

        const total = (notes2000 * 2000) + (notes500 * 500);

        if (total === 4000) {
            document.getElementById('result').innerText = `✅ Total is ₹${total}`;
        } else {
            document.getElementById('result').innerText = `❌ Total is ₹${total}, which is not ₹4000`;
        }
    }

    function calculateCashBreakdown() {
        const denominations = [{
                id: 'notes_2000',
                value: 2000,
                sumId: 'sum_2000'
            },
            {
                id: 'notes_500',
                value: 500,
                sumId: 'sum_500'
            },
            {
                id: 'notes_200',
                value: 200,
                sumId: 'sum_200'
            },
            {
                id: 'notes_100',
                value: 100,
                sumId: 'sum_100'
            },
        ];

        let total = 0;
        let notesum=0;
        const cash = document.getElementById('cash').value;
        const change = document.getElementById('change').value;
        denominations.forEach(note => {
            const count = parseInt(document.getElementById(note.id).value) || 0;
            const subtotal = count * note.value;
            total += subtotal;
            document.getElementById(note.sumId).textContent = `₹${subtotal.toLocaleString()}`;
        });
        
        document.getElementById('totalNoteCash').textContent = ` ₹${total.toLocaleString()}`;

        total -= change;

        if (cash == total) {
            document.getElementById('paymentSubmit').style.display = 'block';

            document.getElementById('result').textContent = `Total Cash: ₹${total.toLocaleString()}`;
        }
    }

    // Run on load
    document.addEventListener("DOMContentLoaded", calculateCashBreakdown);
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
<script>
    let stream;

    navigator.mediaDevices.getUserMedia({
        video: true
    }).then(mediaStream => {
        stream = mediaStream;
        document.getElementById('video1').srcObject = mediaStream;
        document.getElementById('video2').srcObject = mediaStream;
    });

    function captureImage(type) {
        const video = document.getElementById(type === 'product' ? 'video1' : 'video2');
        const canvas = document.getElementById(type === 'product' ? 'canvas1' : 'canvas2');
        const input = document.getElementById(type === 'product' ? 'productImageInput' : 'userImageInput');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob(blob => {
            const formData = new FormData();
            formData.append('photo', blob, 'captured_image.png');
            formData.append('type', type);
            const commissionUserInput = document.getElementById('commissionUser');
            if (commissionUserInput) {
                formData.append('selectedCommissionUser', commissionUserInput.value);
            }
            const partyUserInput = document.getElementById('partyUser');
            if (partyUserInput) {
                formData.append('selectedPartyUser', partyUserInput.value);
            }
            fetch('{{ route('products.uploadpic') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.path) {

                        if (type === 'product') {
                            document.getElementById('step1').classList.add('d-none');
                            document.getElementById('step2').classList.remove('d-none');
                        } else if (type === 'user') {
                            $('#captureModal').modal('hide');
                            $('.modal-backdrop.show').remove();
                            //bootstrap.Modal.getInstance(document.getElementById('captureModal')).hide();
                            //document.getElementById('submitDiv').classList.remove('d-none');

                        }


                    } else {
                        alert('Upload failed!');
                    }
                })
                .catch(err => console.log(err));
        }, 'image/png');


    }

    $(document).ready(function() {
        $('#captureModal').on('hidden.bs.modal', function() {
            // Reset to Step 1 when modal is closed
            document.getElementById('step1').classList.remove('d-none');
            document.getElementById('step2').classList.add('d-none');
        });
    });
</script>
