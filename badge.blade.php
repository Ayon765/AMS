@props(['status'])
<span class="px-2 py-0.5 rounded text-xs {{ \App\Models\StockDocument::COLORS[$status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($status) }}</span>
