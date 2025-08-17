// SendWhatsApp.tsx
import { useState } from 'react';

export default function SendWhatsApp() {
  const [to, setTo] = useState('');
  const [body, setBody] = useState('');
  const [status, setStatus] = useState<string | null>(null);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus('Sending...');
    const res = await fetch('/api/whatsapp/send', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ to, body })
    });
    const json = await res.json();
    setStatus(res.ok ? `Sent: ${json.messages?.[0]?.id ?? ''}` : `Error: ${json.error}`);
  };

  return (
    <form onSubmit={submit}>
      <input className='border p-2 m-2' placeholder="Recipient (e.g., 8801XXXXXXX)" value={to} onChange={e=>setTo(e.target.value)} />
      <input className='border p-2 m-2' placeholder="Message" value={body} onChange={e=>setBody(e.target.value)} />
      <button className='border p-2 m-2' type="submit">Send</button>
      {status && <div>{status}</div>}
    </form>
  );
}
