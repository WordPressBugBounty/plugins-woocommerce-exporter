/**
 * Visual definitions (icon, colours) for each export type slug.
 * Keeps UI concerns out of the REST response.
 */
import {
  Package,
  LayoutGrid,
  ShoppingBag,
  Users,
  Tag,
  RefreshCw,
  Hash,
  Star,
  User,
  MessageSquare,
  Store,
  DollarSign,
  Truck,
  Ticket,
  Calendar,
  SlidersHorizontal,
  Award
} from 'lucide-vue-next'

export const TYPE_VISUALS = {
  product: {
    icon: Package,
    iconColor: '#6C7BFF',
    bgColor: '#EFF1FF'
  },
  category: {
    icon: LayoutGrid,
    iconColor: '#16A34A',
    bgColor: '#F0FDF4'
  },
  order: {
    icon: ShoppingBag,
    iconColor: '#F97316',
    bgColor: '#FFF7ED'
  },
  customer: {
    icon: Users,
    iconColor: '#0D9488',
    bgColor: '#F0FDFA'
  },
  coupon: {
    icon: Tag,
    iconColor: '#DC2626',
    bgColor: '#FFF1F2'
  },
  subscription: {
    icon: RefreshCw,
    iconColor: '#0891B2',
    bgColor: '#ECFEFF'
  },
  tag: {
    icon: Hash,
    iconColor: '#7C3AED',
    bgColor: '#F5F3FF'
  },
  brand: {
    icon: Award,
    iconColor: '#B45309',
    bgColor: '#FFFBEB'
  },
  user: {
    icon: User,
    iconColor: '#7986FF',
    bgColor: '#EFF1FF'
  },
  review: {
    icon: MessageSquare,
    iconColor: '#F59E0B',
    bgColor: '#FFFBEB'
  },
  yith_subscription: {
    icon: RefreshCw,
    iconColor: '#0284C7',
    bgColor: '#F0F9FF'
  },
  product_vendor: {
    icon: Store,
    iconColor: '#059669',
    bgColor: '#ECFDF5'
  },
  wcv_product: {
    icon: Package,
    iconColor: '#10B981',
    bgColor: '#D1FAE5'
  },
  commission: {
    icon: DollarSign,
    iconColor: '#7986FF',
    bgColor: '#EFF1FF'
  },
  shipping_class: {
    icon: Truck,
    iconColor: '#F97316',
    bgColor: '#FFF7ED'
  },
  ticket: {
    icon: Ticket,
    iconColor: '#EC4899',
    bgColor: '#FDF2F8'
  },
  booking: {
    icon: Calendar,
    iconColor: '#8B5CF6',
    bgColor: '#F5F3FF'
  },
  attribute: {
    icon: SlidersHorizontal,
    iconColor: '#0EA5E9',
    bgColor: '#F0F9FF'
  }
}

export const DEFAULT_VISUAL = {
  icon: Package,
  iconColor: '#6B7280',
  bgColor: '#F9FAFB'
}
