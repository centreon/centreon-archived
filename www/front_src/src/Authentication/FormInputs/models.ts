export enum InputType {
  Switch,
  Radio,
  Text,
  Multiple,
  Password,
}

export interface InputProps {
  additionalLabel?: string;
  category: string;
  change?: ({ setFieldValue, value }) => void;
  fieldName: string;
  getChecked?: (value) => boolean;
  label: string;
  options?: Array<{
    isChecked: (value) => boolean;
    label: string;
    value: boolean;
  }>;
  required?: boolean;
  type: InputType;
}

export interface Category {
  name: string;
  order: number;
}
