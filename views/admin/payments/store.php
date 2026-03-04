<?php
/**
 * Club Store Management Page
 * Manage products (uniforms, badges, scarves, merchandise)
 */

$formatMoney = function($cents) {
    return 'R$ ' . number_format($cents / 100, 2, ',', '.');
};
?>

<!-- Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
            <a href="<?= base_url($tenant['slug'] . '/admin/financeiro') ?>" style="color: var(--text-muted); text-decoration: none;">
                <span class="material-icons-round" style="font-size: 1.2rem;">arrow_back</span>
            </a>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0;">Loja do Clube</h1>
        </div>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">Gerencie uniformes, insígnias, lenços e outros produtos</p>
    </div>
    <button onclick="document.getElementById('new-product-modal').classList.remove('hidden')" class="btn btn-primary">
        <span class="material-icons-round">add</span>
        Novo Produto
    </button>
</div>

<!-- Products Grid -->
<?php if (empty($products)): ?>
    <div class="dashboard-card" style="align-items: center; text-align: center; padding: 3rem;">
        <div style="width: 80px; height: 80px; background: rgba(236, 72, 153, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #db2777; margin-bottom: 1rem;">
            <span class="material-icons-round" style="font-size: 2.5rem;">storefront</span>
        </div>
        <h3 style="margin: 0 0 0.5rem 0; font-weight: 700;">Nenhum produto cadastrado</h3>
        <p style="color: var(--text-muted); margin: 0 0 1.5rem 0;">Cadastre uniformes, insígnias, lenços e outros itens para venda online.</p>
        <button onclick="document.getElementById('new-product-modal').classList.remove('hidden')" class="btn btn-primary">
            <span class="material-icons-round">add</span>
            Cadastrar Primeiro Produto
        </button>
    </div>
<?php else: ?>
    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
        <?php foreach ($products as $product): ?>
        <div class="dashboard-card" style="overflow: hidden;">
            <?php if ($product['image_url']): ?>
                <div style="width: 100%; height: 160px; background: url('<?= htmlspecialchars($product['image_url']) ?>') center/cover no-repeat; border-radius: 8px; margin-bottom: 1rem;"></div>
            <?php else: ?>
                <div style="width: 100%; height: 120px; background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;">
                    <span class="material-icons-round" style="font-size: 3rem; color: #0ea5e9; opacity: 0.3;">inventory_2</span>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                <h4 style="margin: 0; font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($product['name']) ?></h4>
                <span style="font-size: 0.65rem; background: <?= $product['is_active'] ? '#dcfce7' : '#fef2f2' ?>; color: <?= $product['is_active'] ? '#15803d' : '#dc2626' ?>; padding: 2px 6px; border-radius: 4px; white-space: nowrap;">
                    <?= $product['is_active'] ? 'Ativo' : 'Inativo' ?>
                </span>
            </div>

            <?php if ($product['category']): ?>
                <span style="font-size: 0.7rem; background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-bottom: 0.5rem;">
                    <?= htmlspecialchars($product['category']) ?>
                </span>
            <?php endif; ?>

            <?php if ($product['description']): ?>
                <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0 0 0.75rem 0; line-height: 1.4;"><?= htmlspecialchars(mb_substr($product['description'], 0, 100)) ?></p>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 0.75rem; border-top: 1px solid var(--border-color);">
                <span style="font-size: 1.25rem; font-weight: 800; color: #10b981;"><?= $formatMoney($product['price_cents']) ?></span>
                <?php if ($product['stock'] !== null): ?>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                        <span class="material-icons-round" style="font-size: 0.85rem; vertical-align: middle;">inventory</span>
                        <?= $product['stock'] ?> em estoque
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Recent Orders -->
<?php if (!empty($orders)): ?>
<div class="dashboard-card" style="margin-top: 2rem;">
    <div class="dashboard-card-header">
        <span class="material-icons-round" style="color: #6366f1;">shopping_cart</span>
        <h3>Pedidos Recentes</h3>
    </div>
    <div class="dashboard-card-body" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Comprador</th>
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Total</th>
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Status</th>
                    <th style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-weight: 600;">Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <?php
                    $statusMap = [
                        'pending' => ['#f59e0b', '#fffbeb', 'Pendente'],
                        'paid' => ['#10b981', '#f0fdf4', 'Pago'],
                        'shipped' => ['#3b82f6', '#eff6ff', 'Enviado'],
                        'delivered' => ['#10b981', '#f0fdf4', 'Entregue'],
                        'cancelled' => ['#ef4444', '#fef2f2', 'Cancelado'],
                    ];
                    $s = $statusMap[$order['status']] ?? ['#94a3b8', '#f8fafc', $order['status']];
                ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 0.75rem 0.5rem; font-weight: 600;"><?= htmlspecialchars($order['buyer_name'] ?? '-') ?></td>
                    <td style="padding: 0.75rem 0.5rem; font-weight: 700;"><?= $formatMoney($order['total_cents']) ?></td>
                    <td style="padding: 0.75rem 0.5rem;">
                        <span style="background: <?= $s[1] ?>; color: <?= $s[0] ?>; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;"><?= $s[2] ?></span>
                    </td>
                    <td style="padding: 0.75rem 0.5rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- New Product Modal -->
<div id="new-product-modal" class="hidden" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 500px; width: 100%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; font-weight: 700;">Novo Produto</h3>
            <button onclick="document.getElementById('new-product-modal').classList.add('hidden')" style="background: none; border: none; cursor: pointer; color: var(--text-muted);">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="create-product-form" enctype="multipart/form-data" style="display: grid; gap: 1rem;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Nome do Produto *</label>
                <input type="text" name="name" placeholder="Ex: Uniforme Completo" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Descrição</label>
                <textarea name="description" rows="2" placeholder="Descritivo do produto..."
                    style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; resize: vertical;"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Preço (R$) *</label>
                    <input type="number" name="price" step="0.01" min="0.01" placeholder="89.90" required
                        style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Estoque</label>
                    <input type="number" name="stock" min="0" placeholder="Ilimitado"
                        style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                </div>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Categoria</label>
                <select name="category" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: white;">
                    <option value="">Sem categoria</option>
                    <option value="uniforme">Uniforme</option>
                    <option value="insignia">Insígnia</option>
                    <option value="lenco">Lenço</option>
                    <option value="livro">Livro / Manual</option>
                    <option value="acessorio">Acessório</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Imagem do Produto</label>
                <input type="file" name="image" accept="image/*"
                    style="width: 100%; padding: 8px; border: 1px dashed var(--border-color); border-radius: 8px; background: var(--bg-hover);">
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                <span class="material-icons-round">check</span>
                Cadastrar Produto
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('create-product-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    try {
        const resp = await fetch('<?= base_url($tenant['slug'] . '/admin/pagamentos/produtos') ?>', {
            method: 'POST', body: new FormData(this)
        });
        const data = await resp.json();
        if (data.success) {
            if (typeof showToast !== 'undefined') showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            if (typeof showToast !== 'undefined') showToast(data.error, 'error');
            else alert(data.error);
            btn.disabled = false;
        }
    } catch (err) { alert('Erro'); btn.disabled = false; }
});
</script>

<style>
.hidden { display: none !important; }
</style>
