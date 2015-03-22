using Microsoft.Owin;
using Owin;

[assembly: OwinStartupAttribute(typeof(WallyApp.Startup))]
namespace WallyApp
{
    public partial class Startup
    {
        public void Configuration(IAppBuilder app)
        {
            ConfigureAuth(app);
        }
    }
}
