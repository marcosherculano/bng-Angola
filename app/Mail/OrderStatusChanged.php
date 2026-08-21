<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $event;
    public $subjectText;

    public function __construct(Order $order, string $event)
    {
        $this->order = $order;
        $this->event = $event;
        $this->subjectText = $this->makeSubject($order, $event);
    }

    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('emails.order_status_changed');
    }

    protected function makeSubject(Order $order, string $event): string
    {
        $id = (string) $order->id;

        switch ($event) {
            case 'delivery_requested':
                return "Pedido #{$id}: entrega solicitada";
            case 'delivery_in_progress':
                return "Pedido #{$id}: entrega em curso";
            case 'delivery_cancelled':
                return "Pedido #{$id}: entrega cancelada";
            case 'schedule_confirmed':
                return "Pedido #{$id}: agendamento confirmado";
            case 'ready_for_pickup':
                return "Pedido #{$id}: pronto para levantamento";
            case 'delivered':
                return "Pedido #{$id}: entregue";
            case 'cancelled':
                return "Pedido #{$id}: cancelado";
            default:
                return "Atualização do pedido #{$id}";
        }
    }
}
