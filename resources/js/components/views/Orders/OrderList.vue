<template>
    <div>
        <h1>Orders</h1>
        <button @click="showAddOrderModal">Add Order</button>

        <!-- Orders Table -->
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Wool Color</th>
                    <th>Size (cm)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="order in orders" :key="order.id">
                    <td>{{ order.id }}</td>
                    <td>{{ order.product_name }}</td>
                    <td>{{ order.quantity }}</td>
                    <td>{{ order.wool_color }}</td>
                    <td>{{ order.size_cm }}</td>
                    <td>{{ order.status }}</td>
                    <td>
                        <button @click="assignArtisan(order.id)">Assign Artisan</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Add Order Modal -->
        <!-- Add your modal for creating orders -->

        <!-- Assign Artisan Modal -->
        <!-- Modal to assign artisan to the selected order -->
    </div>
</template>

<script>
export default {
    data() {
        return {
            orders: [],
        };
    },
    mounted() {
        this.fetchOrders();
    },
    methods: {
        async fetchOrders() {
            const response = await fetch('/api/orders');
            this.orders = await response.json();
        },
        showAddOrderModal() {
            // Show the add order modal
        },
        async assignArtisan(orderId) {
            const artisanId = prompt('Enter artisan ID:'); // Get artisan ID from the user
            const quantity = prompt('Enter assigned quantity:'); // Get the quantity

            await fetch(`/api/orders/${orderId}/assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    employee_id: artisanId,
                    assigned_quantity: quantity,
                }),
            });

            this.fetchOrders(); // Refresh the order list
        },
    },
};
</script>
