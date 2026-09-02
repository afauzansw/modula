import StudentController from '@/actions/App/Http/Controllers/Admin/StudentController';
import { UserDirectoryTable } from '@/components/user-directory-table';
import { index } from '@/routes/admin/students';

export default function StudentsIndex() {
    return (
        <UserDirectoryTable
            title="Student"
            description="Students on the platform"
            noun="student"
            fetchUrl={StudentController.fetch.url()}
            bulkStatusForm={StudentController.bulkUpdateStatus.form()}
        />
    );
}

StudentsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Student',
            href: index(),
        },
    ],
};
