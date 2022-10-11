import { useTranslation } from 'react-i18next';
import { makeStyles } from 'tss-react/mui';

import { MenuItem, MenuItemProps, Theme, Tooltip } from '@mui/material';

import { labelActionNotPermitted } from '../../translatedLabels';

type Props = {
  label: string;
  permitted: boolean;
} & Pick<MenuItemProps, 'onClick' | 'disabled'>;

const useStyles = makeStyles()((theme: Theme) => ({
  menuItem: {
    '&.Mui-selected': {
      '&:hover': {
        background: theme.palette.primary.dark,
      },
      background: theme.palette.primary.dark,
    },
    '&:hover': {
      background: theme.palette.primary.dark,
    },
  },
}));

const ActionMenuItem = ({
  permitted,
  label,
  onClick,
  disabled,
}: Props): JSX.Element => {
  const { classes } = useStyles();
  const { t } = useTranslation();

  const title = permitted ? '' : t(labelActionNotPermitted);

  return (
    <Tooltip title={title}>
      <div>
        <MenuItem
          className={classes.menuItem}
          disabled={disabled}
          onClick={onClick}
        >
          {t(label)}
        </MenuItem>
      </div>
    </Tooltip>
  );
};

export default ActionMenuItem;
