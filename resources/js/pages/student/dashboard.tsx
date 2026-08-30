import { Head } from '@inertiajs/react';
import { Award, BookOpen, CheckCircle2, Clock } from 'lucide-react';
import Heading from '@/components/heading';
import { MockupTable } from '@/components/mockup-table';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

const stats = [
    { label: 'Total Courses', icon: BookOpen },
    { label: 'In Progress', icon: Clock },
    { label: 'Completed', icon: CheckCircle2 },
    { label: 'Certificates Earned', icon: Award },
];

export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Dashboard"
                    description="An overview of your courses and learning progress"
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {stats.map(({ label, icon: Icon }) => (
                        <Card key={label}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-muted-foreground">
                                    {label}
                                </CardTitle>
                                <Icon className="size-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-semibold">
                                    —
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="space-y-3">
                    <h2 className="text-sm font-medium">
                        Continue Learning
                    </h2>
                    <MockupTable
                        columns={['Course', 'Progress', 'Last Accessed']}
                    />
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
