export enum InputType {
  Switch,
  Radio,
  Text,
  Multiple,
  Password,
}

export interface InputProps {
  change?: ({ setFieldValue, value }) => void;
  fieldName: string;
  getChecked?: (value) => boolean;
  getDisabled?: (values) => boolean;
  label: string;
  options?: Array<{
    isChecked: (value) => boolean;
    label: string;
    value: boolean;
  }>;
  type: InputType;
}
