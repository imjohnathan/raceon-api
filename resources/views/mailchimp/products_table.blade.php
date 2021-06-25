<div class="table-responsive">

    <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">圖片</th>
            <th scope="col">資訊</th>
            <th scope="col">動作</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($products["products"] as $product)
          <tr>
            <th scope="row">{{ $product["id"] }}</th>
            <td><img style="width:100px;" src="{{ $product["image_url"] }}"/></td>
            <td>
                <div class="badge bg-secondary">{{ $product["type"] }}</div>
                <div><a href="{{ $product["url"] }}">{{ $product["title"] }}</a></div>
                <p style="white-space: pre-line;">{!! $product["description"] !!}</p>
            </td>
            <td>
                <div class="d-flex justify-content-between flex-wrap align-items-center">
                <a href="?edit={{ $product["id"] }}" class="m-2 btn btn-sm btn-outline-primary">編輯</a>
                <a href="?del={{ $product["id"] }}" class="m-2 btn btn-sm btn-outline-danger">刪除</a>
                </div>
            </td>
          </tr>
            @endforeach
        </tbody>
      </table>