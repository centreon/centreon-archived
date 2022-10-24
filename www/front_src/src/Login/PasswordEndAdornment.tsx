import { useTranslation } from 'react-i18next';

import { InputAdornment } from '@mui/material';
import VisibilityIcon from '@mui/icons-material/Visibility';
import VisibilityOffIcon from '@mui/icons-material/VisibilityOff';

import { IconButton } from '@centreon/ui';

import {
  labelDisplayThePassword,
  labelHideThePassword
} from './translatedLabels';

interface Props {
  changeVisibility: () => void;
  isVisible: boolean;
}

const PasswordEndAdornment = ({
  isVisible,
  changeVisibility
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const icon = isVisible ? <VisibilityOffIcon /> : <VisibilityIcon />;
  const label = isVisible ? labelHideThePassword : labelDisplayThePassword;

  return (
    <InputAdornment position="end">
      <IconButton ariaLabel={t(label)} size="small" onClick={changeVisibility}>
        {icon}
      </IconButton>
    </InputAdornment>
  );
};

export default PasswordEndAdornment;
