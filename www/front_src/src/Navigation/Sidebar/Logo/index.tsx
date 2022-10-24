import makeStyles from '@mui/styles/makeStyles';

import logoLight from './Centreon_Logo_Blanc.svg';

interface Props {
  onClick?: () => void;
}
const useStyles = makeStyles((theme) => ({
  logo: {
    height: theme.spacing(5),
    width: theme.spacing(16.9)
  }
}));

const Logo = ({ onClick }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div aria-hidden onClick={onClick}>
      <img alt="logo" className={classes.logo} src={logoLight} />
    </div>
  );
};

export default Logo;
