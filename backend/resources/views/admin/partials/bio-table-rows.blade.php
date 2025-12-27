@forelse($bioPages as $page)
    <tr>
        <td class="checkbox-cell">
            <input type="checkbox" class="row-checkbox custom-checkbox" value="{{ $page->id }}"
                onchange="updateBulkState()">
        </td>
        <td>
            <div class="org-cell">
                @if($page->logo_path)
                    <img src="{{ asset('storage/' . $page->logo_path) }}" alt="Logo" class="table-logo">
                @else
                    <div class="table-logo-placeholder">
                        <i class="fa-solid fa-image"></i>
                    </div>
                @endif
                <span class="org-name">{{ $page->name }}</span>
            </div>
        </td>
        <td>{{ \Carbon\Carbon::parse($page->created_at)->format('M d, Y') }}</td>
        <td>
            @if($page->expires_at)
                @php
                    $expiryDate = \Carbon\Carbon::parse($page->expires_at);
                    $daysRemaining = now()->diffInDays($expiryDate, false);
                    $isExpiringSoon = $daysRemaining <= 7 && $daysRemaining > 0;
                    $isExpired = $daysRemaining <= 0;
                @endphp
                <div class="expiry-cell {{ $isExpired ? 'expired' : ($isExpiringSoon ? 'expiring' : '') }}"
                    onclick="openExpiryModal('{{ $page->id }}', '{{ addslashes($page->name) }}', '{{ $expiryDate->format('Y-m-d') }}')"
                    style="cursor: pointer;" title="Click to update expiry">
                    {{ $expiryDate->format('M d, Y') }}
                    @if($isExpiringSoon)
                        <span class="expiry-warning" title="Expiring soon!">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </span>
                    @endif
                </div>
            @else
                <span class="text-muted" onclick="openExpiryModal('{{ $page->id }}', '{{ addslashes($page->name) }}', '')"
                    style="cursor: pointer; text-decoration: underline;" title="Set expiry date">Not Set</span>
            @endif
        </td>
        <td>
            <label class="switch">
                <input type="checkbox" onchange="toggleStatus('{{ $page->id }}', this)" {{ $page->status === 'active' ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>
        </td>
        <td>
            <select onchange="updatePayment('{{ $page->id }}', this)" class="payment-select {{ $page->payment_status }}">
                <option value="paid" {{ $page->payment_status === 'paid' ? 'selected' : '' }}>Paid
                </option>
                <option value="unpaid" {{ $page->payment_status === 'unpaid' ? 'selected' : '' }}>
                    Unpaid</option>
            </select>
        </td>
        <td>
            <div class="actions">
                <a href="{{ url('/bio/' . $page->id) }}" target="_blank" class="btn-icon view" title="View Page">
                    <i class="fa-solid fa-link"></i>
                </a>
                <div class="dropdown">
                    <button class="btn-icon download" title="Download QR">
                        <i class="fa-solid fa-download"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="{{ route('admin.bio.download-qr', ['id' => $page->id, 'format' => 'png']) }}"
                            class="dropdown-item">
                            <i class="fa-solid fa-image"></i> PNG
                        </a>
                        <a href="{{ route('admin.bio.download-qr', ['id' => $page->id, 'format' => 'jpeg']) }}"
                            class="dropdown-item">
                            <i class="fa-solid fa-file-image"></i> JPEG
                        </a>
                        <a href="{{ route('admin.bio.download-qr', ['id' => $page->id, 'format' => 'svg']) }}"
                            class="dropdown-item">
                            <i class="fa-solid fa-bezier-curve"></i> SVG
                        </a>
                        <a href="{{ route('admin.bio.download-qr', ['id' => $page->id, 'format' => 'pdf']) }}"
                            class="dropdown-item">
                            <i class="fa-solid fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
                <button onclick="openRenewModal('{{ $page->id }}', '{{ addslashes($page->name) }}')" class="btn-icon renew"
                    title="Renew Subscription">
                    <i class="fa-solid fa-calendar-plus"></i>
                </button>
                <button onclick="confirmDelete('{{ $page->id }}')" class="btn-icon delete" title="Delete">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" style="text-align: center; padding: 2rem;">No bio pages found.</td>
    </tr>
@endforelse
<tr>
    <td colspan="7" style="padding: 0;">
        <div class="pagination">
            {{ $bioPages->links('vendor.pagination.admin-custom') }}
        </div>
    </td>
</tr>