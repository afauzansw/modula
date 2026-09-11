import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import { ConfirmDialog } from '@/components/confirm-dialog';
import { Button } from '@/components/ui/button';
import type { CategoryListItem } from '@/types';
import { CategoryFormDialog } from './category-form-dialog';
import { Pencil, Trash2 } from 'lucide-react';

export function CategoryActions({ category }: { category: CategoryListItem }) {
    return (
        <div className="flex justify-end gap-0.5">
            <CategoryFormDialog
                category={category}
                trigger={
                    <Button variant="ghost" size="sm">
                        <Pencil className="h-3 w-3" />
                    </Button>
                }
            />
            <ConfirmDialog
                trigger={
                    <Button variant="ghost" size="sm">
                        <Trash2 className="h-3 w-3 text-destructive" />
                    </Button>
                }
                title={`Delete "${category.name}"?`}
                description="Any courses in this category become uncategorized. This cannot be undone."
                form={CategoryController.destroy.form(category.id)}
                confirmLabel="Delete category"
            />
        </div>
    );
}
