<p>A new {{ $lead->category->name }} project is available in {{ $lead->city->name }}.</p>
<p>Timeline: {{ str_replace('_', ' ', $lead->timeline ?? 'Not specified') }}<br>
Budget: {{ $lead->budget ?: 'Not specified' }}</p>
<p>Contact details are not included in this email. Sign in to your provider dashboard to review the opportunity and unlock it with credits.</p>
<p><a href="{{ route('provider.leads.show', $lead->id) }}">View lead opportunity</a></p>
