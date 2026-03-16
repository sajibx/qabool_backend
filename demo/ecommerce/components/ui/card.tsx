import { HTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

interface CardProps extends HTMLAttributes<HTMLDivElement> {
  variant?: 'default' | 'elevated' | 'outlined';
}

export const Card = ({ className, variant = 'default', ...props }: CardProps) => {
  const variants = {
    default: 'bg-white rounded-xl shadow-sm',
    elevated: 'bg-white rounded-xl shadow-lg',
    outlined: 'bg-white rounded-xl border-2 border-gray-200'
  };

  return <div className={cn(variants[variant], className)} {...props} />;
};

export const CardHeader = ({ className, ...props }: HTMLAttributes<HTMLDivElement>) => {
  return <div className={cn('p-6 border-b border-gray-100', className)} {...props} />;
};

export const CardTitle = ({ className, ...props }: HTMLAttributes<HTMLHeadingElement>) => {
  return <h3 className={cn('text-xl font-semibold text-gray-900', className)} {...props} />;
};

export const CardDescription = ({ className, ...props }: HTMLAttributes<HTMLParagraphElement>) => {
  return <p className={cn('text-sm text-gray-600 mt-1', className)} {...props} />;
};

export const CardContent = ({ className, ...props }: HTMLAttributes<HTMLDivElement>) => {
  return <div className={cn('p-6', className)} {...props} />;
};

export const CardFooter = ({ className, ...props }: HTMLAttributes<HTMLDivElement>) => {
  return <div className={cn('p-6 border-t border-gray-100', className)} {...props} />;
};