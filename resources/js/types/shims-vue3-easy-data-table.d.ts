declare module 'vue3-easy-data-table' {
  import { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
  export interface Header {
    text: string
    value: string
    sortable?: boolean
    width?: string | number
  }
  export interface Item {
    [key: string]: any
  }
}
