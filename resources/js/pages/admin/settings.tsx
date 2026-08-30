import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Card, CardContent } from '@/components/ui/card';
import { settings } from '@/routes/admin';

export default function AppSettings() {
    return (
        <>
            <Head title="App Setting" />
            <div className="space-y-6 p-4">
                <Heading
                    title="App Setting"
                    description="Platform-wide configuration"
                />
                <Card>
                    <CardContent className="text-sm text-muted-foreground">
                        Mockup — no settings wired up yet.
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

AppSettings.layout = {
    breadcrumbs: [
        {
            title: 'App Setting',
            href: settings(),
        },
    ],
};
