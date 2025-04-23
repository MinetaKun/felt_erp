@component('mail::message')
# Low Stock Alert

Dear Inventory Manager,

This is to inform you that the following item is running low on stock:

**Item Details:**
- Name: {{ $material->name }}
- Current Quantity: {{ $material->quantity }} {{ $material->unit }}
- Minimum Stock Level: {{ $material->min_stock_level }} {{ $material->unit }}
- Type: {{ $material->type }}
- Color: {{ $material->color }}
- Supplier: {{ $material->supplier ?: 'Not specified' }}
- Location: {{ $material->location ?: 'Not specified' }}

@component('mail::button', ['url' => config('app.url') . '/inventory/raw-materials'])
View Inventory
@endcomponent

Please take necessary action to restock this item.

Thanks,<br>
{{ config('app.name') }}
@endcomponent