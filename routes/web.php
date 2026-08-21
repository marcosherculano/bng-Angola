<?php

use App\Http\Controllers\Admin\ConfiguracoesAdminController;
use App\Http\Controllers\Admin\DatabaseBackupsController;
use App\Http\Controllers\Admin\FarmaciasAdminController;
use App\Http\Controllers\Admin\FiliaisAdminController;
use App\Http\Controllers\Admin\DadosBancariosAdminController;
use App\Http\Controllers\Admin\LogsAdminController;
use App\Http\Controllers\Admin\MensalidadesAdminController;
use App\Http\Controllers\Admin\PainelAdminController;
use App\Http\Controllers\Admin\UsuariosAdminController;
use App\Http\Controllers\Cliente\PainelClienteController;
use App\Http\Controllers\Cliente\BuscaMedicamentosController;
use App\Http\Controllers\Cliente\PedidosClienteController;
use App\Http\Controllers\Cliente\FarmaciasFiliaisClienteController;
use App\Http\Controllers\Cliente\AtividadesClienteController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Pharmacy\MensalidadesFarmaciaController;
use App\Http\Controllers\Pharmacy\MedicinesController;
use App\Http\Controllers\Pharmacy\BranchMedicinesController;
use App\Http\Controllers\Pharmacy\MedicineTransfersController;
use App\Http\Controllers\Pharmacy\PedidosFarmaciaController;
use App\Http\Controllers\Pharmacy\FiliaisFarmaciaController;
use App\Http\Controllers\Pharmacy\AtividadesFarmaciaController;
use App\Http\Controllers\Pharmacy\PaymentSettingsController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Webhooks\YangoWebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('publico.home');
});

Route::get('/media/homepage-video/{video}', [MediaController::class, 'homepageVideo'])->name('media.homepage_video');

Route::post('/webhooks/yango', [YangoWebhookController::class, 'handle'])->middleware('throttle:30,1')->name('webhooks.yango');

Auth::routes();

Route::get('/acesso-negado', function () {
    return view('acesso-negado');
})->name('acesso-negado');

Route::get('/aguardar-aprovacao', function () {
    return view('aguardar-aprovacao');
})->middleware(['auth'])->name('aguardar-aprovacao');

Route::get('/conta-suspensa', function () {
    return view('conta-suspensa');
})->name('conta-suspensa');

Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

    Route::get('/notificacoes', [NotificationsController::class, 'index'])->name('notificacoes.index');
    Route::put('/notificacoes/{notification}/lida', [NotificationsController::class, 'markRead'])->name('notificacoes.read');
    Route::put('/notificacoes/marcar-todas', [NotificationsController::class, 'markAllRead'])->name('notificacoes.read_all');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/cliente/busca', BuscaMedicamentosController::class)
    ->middleware(['auth', 'check.status', 'check.role:client'])
    ->name('cliente.busca');

Route::prefix('cliente')->middleware(['auth', 'check.status', 'check.role:client'])->group(function () {
    Route::get('/pedidos', [PedidosClienteController::class, 'index'])->name('cliente.pedidos.index');
    Route::get('/pedidos/criar/{medicine}', [PedidosClienteController::class, 'create'])->name('cliente.pedidos.create');
    Route::post('/pedidos', [PedidosClienteController::class, 'store'])->name('cliente.pedidos.store');
    Route::get('/pedidos/{order}', [PedidosClienteController::class, 'show'])->name('cliente.pedidos.show');
    Route::get('/pedidos/{order}/estado', [PedidosClienteController::class, 'status'])->middleware('throttle:cliente-pedidos-status')->name('cliente.pedidos.status');
    Route::get('/pedidos/{order}/fatura', [PedidosClienteController::class, 'invoice'])->name('cliente.pedidos.invoice');
    Route::get('/pedidos/{order}/fatura/download', [PedidosClienteController::class, 'invoiceDownload'])->name('cliente.pedidos.invoice.download');
    Route::put('/pedidos/{order}/pagamento', [PedidosClienteController::class, 'submitPayment'])->name('cliente.pedidos.payment.submit');
    Route::put('/pedidos/{order}/cancelar', [PedidosClienteController::class, 'cancel'])->name('cliente.pedidos.cancel');

    Route::get('/farmacias/{pharmacy}/filiais', [FarmaciasFiliaisClienteController::class, 'index'])->name('cliente.farmacias.filiais');
});

Route::prefix('admin')->middleware(['auth', 'check.status', 'check.role:admin'])->group(function () {
    Route::get('/painel', PainelAdminController::class)->name('admin.painel');

    Route::get('/backups', [DatabaseBackupsController::class, 'index'])->name('admin.backups.index');
    Route::post('/backups/gerar', [DatabaseBackupsController::class, 'generate'])->name('admin.backups.generate');
    Route::post('/backups/gerar-completo', [DatabaseBackupsController::class, 'generateFull'])->name('admin.backups.generate_full');
    Route::get('/backups/{backup}/download', [DatabaseBackupsController::class, 'download'])->name('admin.backups.download');
    Route::post('/backups/restaurar', [DatabaseBackupsController::class, 'restore'])->name('admin.backups.restore');

    Route::get('/usuarios', [UsuariosAdminController::class, 'index'])->name('admin.usuarios.index');
    Route::put('/usuarios/{user}/aprovar', [UsuariosAdminController::class, 'approve'])->name('admin.usuarios.approve');
    Route::put('/usuarios/{user}/suspender', [UsuariosAdminController::class, 'suspend'])->name('admin.usuarios.suspend');
    Route::put('/usuarios/{user}/bloquear', [UsuariosAdminController::class, 'block'])->name('admin.usuarios.block');
    Route::put('/usuarios/{user}/reativar', [UsuariosAdminController::class, 'unrestrict'])->name('admin.usuarios.unrestrict');
    Route::delete('/usuarios/{user}', [UsuariosAdminController::class, 'destroy'])->name('admin.usuarios.destroy');

    Route::get('/farmacias', [FarmaciasAdminController::class, 'index'])->name('admin.farmacias.index');
    Route::put('/farmacias/{pharmacy}/toggle-active', [FarmaciasAdminController::class, 'toggleActive'])->name('admin.farmacias.toggleActive');
    Route::put('/farmacias/{pharmacy}/mensalidade', [FarmaciasAdminController::class, 'updateMonthlyFee'])->name('admin.farmacias.updateMonthlyFee');
    Route::put('/farmacias/{pharmacy}/alvara', [FarmaciasAdminController::class, 'updateAlvaraDocument'])->name('admin.farmacias.updateAlvaraDocument');
    Route::get('/farmacias/{pharmacy}/alvara-documento', [FarmaciasAdminController::class, 'alvaraDocument'])->name('admin.farmacias.alvara_document');

    Route::get('/filiais', [FiliaisAdminController::class, 'index'])->name('admin.filiais.index');
    Route::put('/filiais/{branch}/aprovar', [FiliaisAdminController::class, 'approve'])->name('admin.filiais.approve');
    Route::get('/filiais/{branch}/alvara', [FiliaisAdminController::class, 'alvaraDocument'])->name('admin.filiais.alvara_document');
    Route::put('/filiais/{branch}', [FiliaisAdminController::class, 'update'])->name('admin.filiais.update');
    Route::delete('/filiais/{branch}', [FiliaisAdminController::class, 'destroy'])->name('admin.filiais.destroy');

    Route::get('/mensalidades', [MensalidadesAdminController::class, 'index'])->name('admin.mensalidades.index');
    Route::get('/mensalidades/{fee}/comprovativo', [MensalidadesAdminController::class, 'proof'])->name('admin.mensalidades.proof');
    Route::put('/mensalidades/{fee}/aprovar', [MensalidadesAdminController::class, 'approve'])->name('admin.mensalidades.approve');
    Route::put('/mensalidades/{fee}/rejeitar', [MensalidadesAdminController::class, 'reject'])->name('admin.mensalidades.reject');

    Route::get('/dados-bancarios', [DadosBancariosAdminController::class, 'index'])->name('admin.dados_bancarios.index');
    Route::post('/dados-bancarios', [DadosBancariosAdminController::class, 'store'])->name('admin.dados_bancarios.store');
    Route::put('/dados-bancarios/{record}/tornar-atual', [DadosBancariosAdminController::class, 'makeCurrent'])->name('admin.dados_bancarios.makeCurrent');

    Route::get('/logs', [LogsAdminController::class, 'index'])->name('admin.logs.index');
    Route::post('/logs/limpar', [LogsAdminController::class, 'clear'])->name('admin.logs.clear');

    Route::get('/configuracoes', [ConfiguracoesAdminController::class, 'edit'])->name('admin.configuracoes.edit');
    Route::put('/configuracoes', [ConfiguracoesAdminController::class, 'update'])->name('admin.configuracoes.update');

    Route::post('/configuracoes/videos', [ConfiguracoesAdminController::class, 'uploadVideo'])->name('admin.configuracoes.videos.upload');
    Route::put('/configuracoes/videos/{video}/ativar', [ConfiguracoesAdminController::class, 'activateVideo'])->name('admin.configuracoes.videos.activate');
    Route::delete('/configuracoes/videos/{video}', [ConfiguracoesAdminController::class, 'deleteVideo'])->name('admin.configuracoes.videos.delete');
});

Route::prefix('client')->middleware(['auth', 'check.status', 'check.role:client'])->group(function () {
    Route::get('/painel', PainelClienteController::class)->name('client.painel');
    Route::post('/atividades/limpar', [AtividadesClienteController::class, 'clear'])->name('client.atividades.clear');
});

Route::prefix('pharmacy')->name('pharmacy.')->middleware(['auth', 'check.status', 'check.role:pharmacy_normal,pharmacy_matrix,pharmacy_branch', 'check.trial_payment'])->group(function () {
    Route::get('/painel', function () {
        return view('pharmacy.painel');
    })->name('painel');

    Route::post('/atividades/limpar', [AtividadesFarmaciaController::class, 'clear'])->name('atividades.clear');

    Route::get('/pagamentos/configuracoes', [PaymentSettingsController::class, 'edit'])->name('payment_settings.edit');
    Route::put('/pagamentos/configuracoes', [PaymentSettingsController::class, 'update'])->name('payment_settings.update');

    Route::middleware(['check.role:pharmacy_normal,pharmacy_matrix'])->group(function () {
        Route::get('/mensalidades', [MensalidadesFarmaciaController::class, 'index'])->name('mensalidades.index');
        Route::put('/mensalidades/{fee}/enviar-comprovativo', [MensalidadesFarmaciaController::class, 'submitProof'])->name('mensalidades.submitProof');
    });

    Route::middleware(['check.role:pharmacy_matrix'])->group(function () {
        Route::get('/filiais', [FiliaisFarmaciaController::class, 'index'])->name('filiais.index');
        Route::post('/filiais', [FiliaisFarmaciaController::class, 'store'])->name('filiais.store');
        Route::put('/filiais/{branch}', [FiliaisFarmaciaController::class, 'update'])->name('filiais.update');
        Route::put('/filiais/{branch}/toggle-active', [FiliaisFarmaciaController::class, 'toggleActive'])->name('filiais.toggleActive');

        Route::get('/transferencias', [MedicineTransfersController::class, 'create'])->name('transfers.create');
        Route::post('/transferencias', [MedicineTransfersController::class, 'store'])->name('transfers.store');
    });

    Route::middleware(['check.role:pharmacy_branch'])->group(function () {
        Route::get('/filial/medicamentos', [BranchMedicinesController::class, 'index'])->name('branch_medicines.index');
        Route::get('/filial/medicamentos/novo', [BranchMedicinesController::class, 'create'])->name('branch_medicines.create');
        Route::post('/filial/medicamentos', [BranchMedicinesController::class, 'store'])->name('branch_medicines.store');
        Route::get('/filial/medicamentos/{inventory}/editar', [BranchMedicinesController::class, 'edit'])->name('branch_medicines.edit');
        Route::put('/filial/medicamentos/{inventory}', [BranchMedicinesController::class, 'update'])->name('branch_medicines.update');
        Route::delete('/filial/medicamentos/{inventory}', [BranchMedicinesController::class, 'destroy'])->name('branch_medicines.destroy');
    });

    Route::resource('/medicines', MedicinesController::class)->except(['show']);

    Route::get('/orders', [PedidosFarmaciaController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [PedidosFarmaciaController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/comprovativo-pagamento', [PedidosFarmaciaController::class, 'paymentProof'])->name('orders.paymentProof');
    Route::put('/orders/{order}/confirm-schedule', [PedidosFarmaciaController::class, 'confirmSchedule'])->name('orders.confirmSchedule');
    Route::put('/orders/{order}/confirm-payment', [PedidosFarmaciaController::class, 'confirmPayment'])->name('orders.confirmPayment');
    Route::put('/orders/{order}/reject-payment', [PedidosFarmaciaController::class, 'rejectPayment'])->name('orders.rejectPayment');
    Route::put('/orders/{order}/delivery', [PedidosFarmaciaController::class, 'updateDeliveryDetails'])->name('orders.delivery.update');
    Route::put('/orders/{order}/request-delivery', [PedidosFarmaciaController::class, 'requestDelivery'])->name('orders.requestDelivery');
    Route::put('/orders/{order}/start-delivery', [PedidosFarmaciaController::class, 'startDelivery'])->name('orders.startDelivery');
    Route::put('/orders/{order}/cancel-delivery', [PedidosFarmaciaController::class, 'cancelDelivery'])->name('orders.cancelDelivery');
    Route::put('/orders/{order}/ready', [PedidosFarmaciaController::class, 'markReady'])->name('orders.ready');
    Route::put('/orders/{order}/delivered', [PedidosFarmaciaController::class, 'markDelivered'])->name('orders.delivered');
    Route::put('/orders/{order}/cancel', [PedidosFarmaciaController::class, 'cancel'])->name('orders.cancel');
});
