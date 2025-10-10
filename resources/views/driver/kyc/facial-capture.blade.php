@extends('layouts.driver')

@section('content')
<div class="container mt-5">
  <div class="card shadow-sm p-4">
    <h3 class="text-center mb-4">Facial Recognition Capture</h3>
    <p class="text-center text-muted">Please ensure your face is clearly visible and well-lit.</p>

    <form method="POST" action="{{ route('driver.kyc.facial.submit') }}" enctype="multipart/form-data">
      @csrf
      <div class="text-center mb-3">
        <video id="camera" autoplay playsinline width="300" height="230" class="border rounded"></video>
        <canvas id="snapshot" width="300" height="230" class="d-none"></canvas>
        <input type="file" name="photo" id="photo" class="form-control mt-3" accept="image/*" capture="user">
      </div>
      <button type="submit" class="btn btn-primary w-100">Submit Facial Capture</button>
    </form>
  </div>
</div>

<script>
  const video = document.getElementById('camera');
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => { video.srcObject = stream; });
  }
</script>
@endsection
