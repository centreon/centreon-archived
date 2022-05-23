import { FormikValues } from 'formik';

export enum InputType {
  Switch,
  Radio,
  Text,
  Multiple,
  Password,
  ConnectedAutocomplete,
}

export interface InputProps {
  additionalLabel?: string;
  category: string;
  change?: ({ setFieldValue, value }) => void;
  endpoint?: string;
  fieldName: string;
  filterKey?: string;
  getChecked?: (value) => boolean;
  getDisabled?: (values: FormikValues) => boolean;
  getRequired?: (values: FormikValues) => boolean;
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
