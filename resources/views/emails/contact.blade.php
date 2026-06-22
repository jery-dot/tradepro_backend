<h2>New Contact Request</h2>
<p><strong>Name:</strong> {{ $formData['user_name'] }}</p>
<p><strong>Email:</strong> {{ $formData['user_email'] }}</p>
<p><strong>Phone:</strong> {{ $formData['user_phone'] }}</p>
<p><strong>Subject:</strong> {{ $formData['subject'] }}</p>
<p><strong>Message:</strong></p>
<p>{{ $formData['message'] }}</p>
<p><strong>Preferred Contact Methods:</strong> {{ implode(', ', $formData['method'] ?? []) }}</p>
