import { Link } from 'react-router-dom';
import { Mail, MessageCircleQuestion } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/components/common/PageHeader';
import { appConfig } from '@/config/appConfig';
import { routePaths } from '@/routes/routePaths';

const faqs = [
  ['How do I request a document?', 'Go to Document Request, choose the document type, state the purpose, and submit. You will receive a request ID to track.'],
  ['How do I track my request?', 'Open Request Status to see every request with its current status, or open Recent Requests on the dashboard.'],
  ['Can I cancel a request?', 'Pending requests can be cancelled from the request details page. Once processing starts, contact Student Services.'],
  ['How do I update my contact details?', 'Open Profile and edit your email or contact number. Official academic records are read-only.'],
];

export default function HelpSupportPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Help & Support" description="How to use the student portal and where to get help." />
      <div className="grid gap-6 lg:grid-cols-2">
        <Card><CardHeader><CardTitle className="flex items-center gap-2"><MessageCircleQuestion className="h-5 w-5 text-primary" aria-hidden="true" />Frequently asked questions</CardTitle></CardHeader>
          <CardContent><dl className="space-y-4">{faqs.map(([q, a]) => (<div key={q}><dt className="text-sm font-semibold">{q}</dt><dd className="mt-1 text-sm text-muted-foreground">{a}</dd></div>))}</dl></CardContent></Card>
        <Card><CardHeader><CardTitle className="flex items-center gap-2"><Mail className="h-5 w-5 text-primary" aria-hidden="true" />Contact Student Services</CardTitle></CardHeader>
          <CardContent className="space-y-3 text-sm text-muted-foreground"><p>For enrollment corrections, request follow-ups, or account issues, reach out to Student Services.</p><p className="font-medium text-foreground">{appConfig.supportEmail}</p>
            <div className="flex flex-wrap gap-2"><Button asChild><a href={`mailto:${appConfig.supportEmail}`}>Email support</a></Button><Button variant="outline" asChild><Link to={routePaths.student.requestStatus}>Track my requests</Link></Button></div></CardContent></Card>
      </div>
    </div>
  );
}
