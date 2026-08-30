import { Head } from '@inertiajs/react';
import { BookOpen, DollarSign, GraduationCap, Star } from 'lucide-react';
import Heading from '@/components/heading';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes/instructor';

const stats = [
    { label: 'Total Courses', icon: BookOpen },
    { label: 'Total Students', icon: GraduationCap },
    { label: 'Total Earnings', icon: DollarSign },
    { label: 'Average Rating', icon: Star },
];

const categoryBreakdown = ['Web Development', 'Design', 'Business'];

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

export default function InstructorDashboard() {
    return (
        <>
            <Head title="Instructor Dashboard" />
            <div className="space-y-6 p-4">
                <Heading
                    title="Dashboard"
                    description="An overview of your courses and earnings"
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
                                <div className="text-2xl font-semibold">—</div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-medium">
                                Courses by Category
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {categoryBreakdown.map((category) => (
                                <div
                                    key={category}
                                    className="flex items-center gap-3"
                                >
                                    <span className="w-32 shrink-0 truncate text-sm text-muted-foreground">
                                        {category}
                                    </span>
                                    <div className="h-2 flex-1 rounded-full bg-muted">
                                        <div className="h-2 w-0 rounded-full bg-chart-1" />
                                    </div>
                                    <span className="w-4 shrink-0 text-right text-sm text-muted-foreground">
                                        —
                                    </span>
                                </div>
                            ))}
                            <p className="pt-1 text-xs text-muted-foreground">
                                Mockup — no data wired up yet.
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-medium">
                                Earnings by Month
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex h-32 items-end gap-2">
                                {months.map((month) => (
                                    <div
                                        key={month}
                                        className="flex flex-1 flex-col items-center gap-2"
                                    >
                                        <div className="h-1.5 w-full max-w-6 rounded-t-sm bg-chart-1" />
                                        <span className="text-xs text-muted-foreground">
                                            {month}
                                        </span>
                                    </div>
                                ))}
                            </div>
                            <p className="pt-3 text-xs text-muted-foreground">
                                Mockup — no data wired up yet.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

InstructorDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
