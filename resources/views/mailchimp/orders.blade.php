<div class="table-responsive">

    <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">時間</th>
            <th scope="col">顧客</th>
            <th scope="col">品項</th>
            <th scope="col">資訊</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($data["orders"] as $order)
          <tr>
            <th scope="row">#{{ $order["id"] }}</th>
            <td>
                {{ date('Y-m-d H:i:s', strtotime($order["processed_at_foreign"])) }}
            </td>
            <td>{{ $order["customer"]["first_name"] }}</td>
            <td><ul>
                @foreach ($order["lines"] as $line)
                <li>{{ $line["product_title"]}}</li>
                @endforeach
            </ul>
            </td>
            <td>
                <ul>
                    <li>訂單金額：{{ $order["order_total"] }}</li>
                    <li>付款狀況：{{ $order["financial_status"] }}</li>
                    <li>出貨狀況：{{ $order["fulfillment_status"] }}</li>
                </ul>
            </td>
          </tr>
            @endforeach
        </tbody>
      </table>